<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

class Dictionary extends ActiveRecord
{
	/**
     * @return array
     */
    public static function next($testId) {

        $result = [];
        
        $sql = 'SELECT d.id, d.en_id, en_w.word AS en, d.ru_id, ru_w.word AS ru FROM dictionary AS d ';
        $sql .= 'LEFT JOIN en_words AS en_w ON d.en_id = en_w.id ';
        $sql .= 'LEFT JOIN ru_words AS ru_w ON d.ru_id = ru_w.id ';
        $sql .= 'LEFT JOIN (SELECT dictionary_id FROM records WHERE test_id = :test_id) AS rec ON d.id = rec.dictionary_id ';
        $sql .= 'WHERE rec.dictionary_id IS NULL';
        $command = Yii::$app->db->createCommand($sql);
        $command->bindParam(':test_id', $testId);
        $dictionary = $command->queryAll();
        
        if (count($dictionary)) {
            
            // Get shuffle languages array
            $languages = array('ru', 'en');
            shuffle($languages);

            // Get random record
	        shuffle($dictionary);

			    $record = $dictionary[0];
	        $result['word'] = $record[$languages[0]];
	        $result['word_id'] = $record[$languages[0]."_id"];
	        $result['translates'][] = array("id"=>$record[$languages[1]."_id"], "word"=>$record[$languages[1]]);

	        $sql = 'SELECT d.id, d.en_id, en_w.word AS en, d.ru_id, ru_w.word AS ru FROM dictionary AS d ';
          $sql .= 'LEFT JOIN en_words AS en_w ON d.en_id = en_w.id ';
          $sql .= 'LEFT JOIN ru_words AS ru_w ON d.ru_id = ru_w.id ';
          $sql .= 'WHERE d.'.$languages[1].'_id <> :id';
          $command = Yii::$app->db->createCommand($sql);
          $command->bindParam(':id', $record[$languages[1]."_id"]);
          $dictionary = $command->queryAll();

	        if (count($dictionary) > 3) {	        
		        // Randomly pick three more translations to add to the result
		        $keys = array_rand($dictionary, 3);
		        $add_words_count = 0;

		        foreach ($keys as $key)
		        {
		            if($dictionary[$key][$languages[1]] != $record[$languages[1]]) {
		                $result['translates'][] = array("id"=>$dictionary[$key][$languages[1]."_id"], "word"=>$dictionary[$key][$languages[1]]);
		            }else{
		                unset($dictionary[$key]);
		                $add_words_count++;
		            }
		        }

		        // Check 'pear' problem
		        shuffle($dictionary);
		        if(count($dictionary) >= $add_words_count) {
                for($ind = 0; $ind < $add_words_count-1; $ind++) {
                     $result['translates'][] = array("id"=>$dictionary[$ind][$languages[1]."_id"], "word"=>$dictionary[$ind][$languages[1]]);
                }
            }else{
                return [];
            }

		        shuffle($result['translates']);

		        $result['language'] = $languages[0];
		    }

		}
        return $result;
    }
}