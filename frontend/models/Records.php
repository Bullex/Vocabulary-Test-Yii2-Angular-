<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "records".
 *
 * @property integer $id
 * @property integer $test_id
 * @property integer $dictionary_id
 * @property integer $language
 * @property string  $wrong_answer_id
 * @property integer $is_error
 *
 * @property Users $user
 * @property Dictionary $dictionary
 * @property Test $test
 */
class Records extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['test_id', 'dictionary_id', 'language'], 'required'],
            [['test_id', 'dictionary_id', 'wrong_answer_id', 'language', 'is_error'], 'integer']

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'test_id' => 'Test ID',
            'dictionary_id' => 'Dictionary ID',
            'language' => 'Language',
            'is_error' => 'Is Error',
            'wrong_answer_id' => 'Wrong translation ID',
            'is_submitted' => 'Is Submitted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDictionary()
    {
        return $this->hasOne(Dictionary::className(), ['id' => 'dictionary_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTest()
    {
        return $this->hasOne(Test::className(), ['id' => 'test_id']);
    }

    /**
     * @return boolean
     */
    public static function hasMaxErrors($test_id)
    {
        return self::find()
                ->where(['test_id' => $test_id, 'is_error' => 1])
                ->count() >= 3;
    }
}
