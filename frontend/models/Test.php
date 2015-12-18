<?php

namespace frontend\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use frontend\models\Dictionary;
use frontend\models\Records;

/**
 * This is the model class for table "test".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $score
 * @property integer $answers_count
 * @property timestamp $started_at
 * @property timestamp $ended_at
 *
 * @property User $user
 * @property TestRecords[] $testRecords
 */
class Test extends \yii\db\ActiveRecord
{
 
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'score' => 'Score',
            'answers_count' => 'Answers count',
            'started_at' => 'Started at',
            'ended_at' => 'Ended at',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTestRecords()
    {
        return $this->hasMany(Records::className(), ['test_id' => 'id']);
    }
    
    /**
     * @return integer
     */
    public function scoreUp()
    {
        $this->score++;
        return $this->update();
    }
    
}