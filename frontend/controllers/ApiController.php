<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use common\models\User;
use frontend\models\ContactForm;
use frontend\models\SignupForm;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\rest\Controller;
use frontend\models\Test;
use frontend\models\Dictionary;
use frontend\models\Languages;
use frontend\models\Records;
use yii\filters\auth\HttpBearerAuth;

/**
 * Site controller
 */
class ApiController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['test'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['test'],
            'rules' => [
                [
                    'actions' => ['test'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionStart()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $user_id = $params['user_id'];

        $test = new Test();
        $test->user_id = $user_id;
        $test->save();
        return ['testId' => $test->id, 'words_count' => Dictionary::find()->count()];
    }

    public function actionNext() 
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $testId = $params['testId'];
        $result = [
            'word' => '',
            'translates' => [],
            'language' => '',
            'success' => false
        ];
        
        $test = Test::findOne($testId);

        if (!$test) {
            // The client supplied a wrong test ID!
            $result['message'] = 'The test was not found';
            return $result;
        }
        
        $next = Dictionary::next($testId);

        if (!count($next)) {
            return $result;
        }

        $next['success'] = true;
        return $next;
    }

    public function actionCheck() 
    {
        $result = [];

        $request = Yii::$app->request;
        $test_id = $request->post('test_id');
        $word_id = $request->post('word_id');
        $answer_id = $request->post('answer_id');
        $language = $request->post('language');
        $username = $request->post('username');


        $user = User::findByUsername($username);
        $test = Test::findOne($test_id);
        $dictionaries = Dictionary::findAll([$language.'_id' => $word_id]);

        if (!$user || !$test) {
            return ['error' => true, 'message' => 'Either the user or test not found'];
        } 

        foreach($dictionaries as $dictionary) {
            $error = $language == 'ru' ?
                    ($dictionary->en_id != $answer_id) :
                    ($dictionary->ru_id != $answer_id);
            if($error) {
                continue;
            }else {
                break;
            }
        }

        if ($error) {
            $result['error'] = $error;
        }
        
        if (Records::hasMaxErrors($test_id)) {
            $result['maxErrors'] = true;
            return $result;
        }

        $lang_record = Languages::findOne(['lang' => $language]);
        
        $testRecord = new Records();
        $testRecord->attributes = [
            'test_id' => $test_id,
            'dictionary_id' => $dictionary->id,
            'language' => $lang_record->id,
            'is_error' => (int)$error,
            'wrong_answer_id' => $error ? $answer_id : null
        ];
        $testRecord->save();
        
        // Check once again for max errors after the save
        if (Records::hasMaxErrors($test_id)) {
            $result['maxErrors'] = true;
            return $result;
        }

        if (!$error) {
            // Increase the score by 1
            $test->scoreUp();
        }

        return $result;
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $params = Yii::$app->getRequest()->getBodyParams();

        if ($model->load($params, '') && $model->login()) {
            return ['access_token' => Yii::$app->user->identity->getAuthKey(), 'user_id' => Yii::$app->user->identity->id];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        $isLoad = $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $user = $model->signup();

        if ($isLoad && $user) {
            Yii::$app->user->login($user);
            return ['access_token' => $user->getAuthKey(), 'user_id' => $user->getId()];
        } else {
            $model->validate();
            return $model;
        }
    }

    public function actionExist()
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $user = User::findByUsername($params);
        if(!$user) {
            $model = new SignupForm();
            $isLoad = $model->load($params, '');
            $user = $model->signup();

            if ($isLoad && $user) {
                Yii::$app->user->login($user);
                return ['access_token' => $user->getAuthKey(), 'user_id' => $user->getId()];
            } else {
                $model->validate();
                return $model;
            }
        }else{
            Yii::$app->user->login($user);
            return ['access_token' => $user->getAuthKey(), 'user_id' => $user->getId()];
        }
        return null;
    }

    public function actionTest()
    {
        $response = [
            'username' => Yii::$app->user->identity->username,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
        ];

        return $response;
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                $response = [
                    'flash' => [
                        'class' => 'success',
                        'message' => 'Thank you for contacting us. We will respond to you as soon as possible.',
                    ]
                ];
            } else {
                $response = [
                    'flash' => [
                        'class' => 'error',
                        'message' => 'There was an error sending email.',
                    ]
                ];
            }
            return $response;
        } else {
            $model->validate();
            return $model;
        }
    }
}
