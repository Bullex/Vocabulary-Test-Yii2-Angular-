<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\Test;

/**
 * Test controller
 */
class TestController extends Controller
{
    public function actionTestStart()
    {
        return "asd";
        $params = Yii::$app->getRequest()->getBodyParams();
        $user_id = $params['user_id'];
        return $user_id;
        // $test = new Test();
        // $test->user_id = $user_id;
        // $test->save();
        // return ['testId' => $test->id];
    }

    public function actionNext() 
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        $testId = $params['testId'];
        $result = [
            'word' => '',
            'translations' => [],
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
        $username = $request->post('username');
        $testId = $request->post('testId');
        $word = $request->post('word');
        $translation = $request->post('translation');
        $order = $request->post('order');

        $user = Users::findByUsername($username);
        $test = Test::findOne($testId);
        if ($order) {
            $dictionary = Dictionary::findOne(['translation' => $word]);
        } else
        {
            $dictionary = Dictionary::findOne(['word' => $word]);
        }

        if (!$user || !$test) {
            return ['error' => true, 'message' => 'Either the user or test not found'];
        } 
        
        $error = $order ? 
                ($dictionary->word !== $translation) :
                ($dictionary->translation !== $translation);
            
        if ($error) {
            $result['error'] = $error;
        }
        
        if (TestRecords::hasMaxErrors($testId)) {
            $result['maxErrors'] = true;
            return $result;
        }
        
        // TODO: transaction here
        $testRecord = new TestRecords();
        $testRecord->attributes = [
            'user_id' => $user->id,
            'test_id' => $testId,
            'dictionary_id' => $dictionary->id,
            'order' => $order,
            'is_error' => (int)$error,
            'wrong_translation' => $error ? $translation : null
        ];
        $testRecord->save();
        
        // Check once again for max errors after the save
        if (TestRecords::hasMaxErrors($testId)) {
            $result['maxErrors'] = true;
            return $result;
        }

        if (!$error) {
            // Increase the score by 1
            $test->scoreUp();
        }

        return $result;
    }
}
