<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 28 Aug 2017 06:57:19 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class DeliveryType
 * 
 * @property int $id
 * @property int $courier_id
 * @property string $description
 * 
 * @property \App\Models\Courier $courier
 * @property \Illuminate\Database\Eloquent\Collection $transactions
 *
 * @package App\Models
 */
class DeliveryType extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'courier_id' => 'int'
	];

	protected $fillable = [
		'courier_id',
		'description'
	];

	public function courier()
	{
		return $this->belongsTo(\App\Models\Courier::class);
	}

	public function transactions()
	{
		return $this->hasMany(\App\Models\Transaction::class);
	}
}