<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Response extends Model
{
    use SoftDeletes;
    protected $fillable = ['response_info', 'user_id'];
    
    protected $casts = [
        'response_info' => 'array',
    ];

    public function session() {
        return $this->belongsTo(Session::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getResponseTextAttribute() {
        $responseInfo = $this->response_info;
        switch($responseInfo["question_type"]) {
            case "multiple_choice": 
            case "slider": 
                 if(is_array($responseInfo["choice"])) {
                    return join(",", $responseInfo["choice"]);
                }
                else {
                    return $responseInfo["choice"];
                }
                break;
            case "free_response": 
                return $responseInfo["text"];
                break;
            case "image_response":
                return $responseInfo["image_name"];
                break;
            case "heatmap_response":
                return $responseInfo["image_coordinates"]["coordinate_x"] . "," . $responseInfo["image_coordinates"]["coordinate_y"];
                break;
            case "text_heatmap_response":
                return $responseInfo["startOffset"] . " - " . $responseInfo["endOffset"];
                break;
            default:
                return "";
                break;
        }
    }
}
