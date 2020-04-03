<?php

namespace App\Models\Admin;

use App\Models\User\User;
use App\Models\User\UserCourse;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name','alias','description','cover_image','enable_quiz','source','is_active','priority','is_public'
    ];

    public function topics()
    {
        return $this->hasMany('App\Models\Admin\Topic');
    }


    public function tags()
    {
        return $this->belongsToMany('App\Models\Admin\Tag','tag_courses');
    }

    public function scopeActiveEnrolledCourses($query, $user_id = 0)
    {
        $user = User::with('activePackages.courses')->find($user_id);
        $course_ids = [];
        foreach ($user->activePackages as $package) {
            foreach ($package->courses as $course) {
                $course_ids[] = $course->id;
            }
        }
        $enrolled_course_ids = UserCourse::where('user_id',$user_id)->pluck('course_id')->toArray();
        $enrolled_course_ids = array_intersect($course_ids,$enrolled_course_ids);
        return $query->where('is_active',1)->whereIn('id',$enrolled_course_ids);
    }

    public function scopeActiveCourses($query, $user_id = 0)
    {
        $user = User::with('activePackages.courses')->find($user_id);
        $course_ids = [];
        foreach ($user->activePackages as $package) {
            foreach ($package->courses as $course) {
                $course_ids[] = $course->id;
            }
        }
        return $query->where('is_active',1)->whereIn('id',$course_ids);
    }
}
