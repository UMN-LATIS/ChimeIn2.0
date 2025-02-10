<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model {
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['name', 'order'];
    protected $casts = [
        'deleted_at' => 'datetime'
    ];
    protected $withCount = ['questions'];

    public function chime() {
        return $this->belongsTo(Chime::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }

    public function lti13_resource_link() {
        return $this->belongsTo(LTI13ResourceLink::class);
    }
    /**
     * updates order attribute for each question in the folder
     * so that they are from 1 to n in order
     **/
    public function normalizeQuestionOrder()
    {
        $this->questions
            ->sortBy('order')
            ->values()
            ->each(function ($question, $index) {
                $question->order = $index + 1;
                $question->save();
            });
    }
}
