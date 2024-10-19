<?php

namespace App\Http\Resources;

use App\Models\UserDiscount;
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
      $trips[] = [
        'id' => $trip->id,
        'object_type' => $trip->object,
        'weight' => $trip->weight,
        'status' => $trip->status,
        'from' => $trip->from,
        'to' => $trip->to,
        'type_id' => $trip->type_id,
        'trip_time' => $trip->created_at
      ];

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