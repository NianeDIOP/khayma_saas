<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowProgress extends Model
{
    protected $table = 'workflow_progress';

    protected $fillable = [
        'phase',
        'step_states',
        'done',
        'total',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'step_states' => 'array',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
    ];
}
