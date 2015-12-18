<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "records".
 *
 * @property integer $id
 * @property string $lang
 */
class Languages extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lang'], 'required'],
            [['lang'], 'string']

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lang' => 'Language',
        ];
    }
}
