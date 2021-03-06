<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 25 Jun 2018 07:50:37 +0000.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TailorMade
 * 
 * @property int $id
 * @property string $email
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $finish_date
 * @property string $destination
 * @property string $participant
 * @property float $budget_per_person
 * @property string $request
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 *
 * @package App\Models
 */
class TailorMade extends Eloquent
{
	protected $table = 'tailor_made';
	public $timestamps = false;

	protected $append = [
	    'start_date_string',
        'finish_date_string'
    ];

	protected $casts = [
		'budget_per_person' => 'float'
	];

	protected $dates = [
		'start_date',
		'finish_date'
	];

	protected $fillable = [
		'email',
		'start_date',
		'finish_date',
		'destination',
		'participant',
		'budget_per_person',
		'request',
		'status_id',
        'created_at'
	];

    public function getStartDateStringAttribute(){
        return Carbon::parse($this->attributes['start_date'])->format('d M Y');
    }

    public function getFinishDateStringAttribute(){
        return Carbon::parse($this->attributes['finish_date'])->format('d M Y');
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\Status::class);
    }
}
