<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewPolls extends Model
{
    public function getAnswers(){
    	$answers = \App\Models\NewPollAnswers::where('status_id', 1)->where('poll_id', $this->id);

    	if($answers->count() > 0){

	    	$answers_data = \App\Models\NewPollAnswerData::selectRaw('answer_id, count(answer_id) as cnt')->where('status_id', 1)->where('poll_id', $this->id)->groupBy('answer_id');

	    	$sum = 0;
	    	$answer_count = [];
	    	if($answers_data->count() > 0){
	    		$answers_data = $answers_data->get();

	    		foreach ($answers_data as $v) {
	    			$sum += $v->cnt;
	    			$answer_count[$v->answer_id] =  $v->cnt;
	    		}
	    	}

	    	$answers = $answers->get()->toArray();

	    	for($i=0;$i< count($answers);$i++){
	    		$count = isset($answer_count[$answers[$i]['id']])?$answer_count[$answers[$i]['id']]:0;
	    		$answers[$i]['count'] = $count;
	    		$answers[$i]['percent'] = round($count * 100 / $sum);
	    	}

    		return $answers;
    	}

    	return [];

    }

    public static function generateShortcode($data)
    {
    	$poll = \App\Models\NewPolls::find($data['id']);
    	$poll_answers = $poll->getAnswers();

    	if(empty($poll)){
    		return 'Video Not Found !';
    	}

    	$html = "";

		$html .= '<div class="Poll" id="poll-'.$poll->id.'" poll-id="'.$poll->id.'">
					<div class="PollTitle">გამოკითხვა</div>
					<div class="PollQuestion">'.$poll->title.'</div>					
					<div class="PollAnswers">';
					foreach($poll_answers as $answers) { 
						$html .= ' <div class="AnswerItem AnswerItem-'.$answers['id'].'" answer-id="'.$answers['id'].'" poll-id="'.$poll->id.'">
										<div class="AnswerItemTitle" answer-id="'.$answers['id'].'" poll-id="'.$poll->id.'">'.$answers['title'].'</div>
										<div class="AnswerItemCount" answer-id="'.$answers['id'].'" poll-id="'.$poll->id.'">'.$answers['percent'].'%</div>
									</div>';
					}
		$html .= 	'</div>
				</div>';

    	return $html;
    }
}
