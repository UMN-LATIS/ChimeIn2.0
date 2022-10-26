<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Response extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['response_info', 'user_id'];

    protected $casts = [
        'response_info' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getQuestionType()
    {
        return $this->response_info['question_type'];
    }

    public function isCorrect(): bool
    {
        if ($this->getQuestionType() !== Question::MULTIPLE_CHOICE_TYPE) {
            return true;
        }

        $multChoiceQuestion = $this->session->question;
        $choiceSet = collect($multChoiceQuestion->getResponseChoices());
        $correctChoices = $choiceSet->filter(fn ($choice) => $choice['correct']);

        // if question choice set has no correct answers
        // then any choice is correct
        if (!$correctChoices->isEmpty()) return true;

        // otherwise the answer is correct only if the response
        // matches some correct choice
        return $correctChoices->contains($this->getResponseTextAttribute());
    }

    public function getResponseTextAttribute()
    {
        $responseInfo = $this->response_info;
        switch ($responseInfo["question_type"]) {
            case Question::MULTIPLE_CHOICE_TYPE:
            case Question::SLIDER_TYPE:
                if (is_array($responseInfo["choice"])) {
                    return join(",", $responseInfo["choice"]);
                } else {
                    return $responseInfo["choice"];
                }
                break;
            case Question::FREE_RESPONSE_TYPE:
                return $responseInfo["text"];
                break;
            case Question::IMAGE_RESPONSE_TYPE:
                return $responseInfo["image_name"];
                break;
            case Question::HEATMAP_RESPONSE_TYPE:
                return $responseInfo["image_coordinates"]["coordinate_x"] . "," . $responseInfo["image_coordinates"]["coordinate_y"];
                break;
            case Question::TEXT_HEATMAP_RESPONSE_TYPE:
                return $responseInfo["startOffset"] . " - " . $responseInfo["endOffset"];
                break;
            default:
                return "";
                break;
        }
    }
}
