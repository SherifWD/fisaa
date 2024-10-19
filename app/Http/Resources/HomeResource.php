<?php

namespace App\Http\Resources;

use App\Models\ObjectWeight;
use App\Models\StuffType;
use App\Models\TripType;
use App\Models\UserDiscount;
use App\Models\Worker;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class HomeResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
   */
  public function toArray($request)
  {
    // dd($this->resource);
    $data = [];
    $trips = [];
    $categories = [];
    // dd($this->resource['trips']);
    foreach ($this->resource['trips'] as $trip) {
      if (TripType::find($trip->type_id)->name == 'carrier') {
        $trips[] =
          [
            'id' => $trip->id,
            'type_name' => TripType::find($trip->type_id)->name,
            'from' => $trip->from,
            'from_lat' => $trip->from_lat,
            'from_lng' => $trip->from_lng,
            'to' => $trip->to,
            'to_lat' => $trip->to_lat,
            'to_lng' => $trip->to_lng,
            'price' => $trip->price,
            'is_cash' => $trip->is_cash,
            'stuff_type_name' => StuffType::find($trip->stuff_type_id)->name,
            'weight_name' => ObjectWeight::find($trip->weight_id)->weight ?? '-',
            'worker_name' => Worker::find($trip->worker_id)->count ?? '-',
            'stuff_type_image' => StuffType::find($trip->stuff_type_id)->image,
            'sender_name' => $trip->sender_name,
            'sender_phone' => $trip->sender_phone,
            'receiver_name' => $trip->receiver_name,
            'receiver_phone' => $trip->receiver_phone,
            'payment_by' => $trip->payment_by,
            'trip_time' => $trip->created_at,
            'status' => $trip->status,
          ];
      } else
        $trips[] =
          // $trip
          [
            'id' => $trip->id,
            'object_type' => $trip->object,
            'weight' => $trip->weight,
            'status' => $trip->status,
            'from' => $trip->from,
            'to' => $trip->to,
            'type_name' => TripType::find($trip->type_id)->name,
            'trip_time' => $trip->created_at
          ]
        ;

    }
    foreach ($this->resource['categories'] as $category) {
      $user_discount = UserDiscount::where('user_id', auth()->user()->id)->where('category_id', $category->id)->first();
      $forced_discount = 1;
      $categories[] = [
        'id' => $category->id,
        'title' => $category->title,
        'short_title' => $category->short_title,
        'is_discount' => $category->is_discount,
        'is_active' => $category->is_active,
        'image' => $category->image,
        'discount' => ($user_discount ? $forced_discount : 0) ? $user_discount->discount : $category->discount,
        'force_user_discount' => 1,

      ];
    }
    $data = [
      'categories' => $categories,
      'trips' => $trips,

    ];




    return $data;
  }
}