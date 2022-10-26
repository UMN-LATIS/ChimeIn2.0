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
        return $this->session->question
            ->isResponseCorrect($this);
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
