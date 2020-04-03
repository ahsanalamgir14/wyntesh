<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\Course;
use App\Models\Admin\Topic;
use App\Models\Admin\Question;
use Validator;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $courses=Course::with('topics')->with('tags')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $courses=Course::select();
            $courses=$courses->orWhere('name','like','%'.$search.'%');
            $courses=$courses->with('topics')->with('tags')->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Courses retrieved.",'data'=>$courses);
        return response()->json($response, 200);
    }

    public function getCourseTopics(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $topics=Topic::where('course_id',$request->course_id)->orderBy('id',$sort)->paginate($limit);    
        }else{
            $topics=Topic::select();
            $topics=$topics->where('name','like','%'.$search.'%');
            $topics=$topics->where('course_id',$request->course_id)->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Topics retrieved.",'data'=>$topics);
        return response()->json($response, 200);
    }

    public function getCourseQuestions(Request $request)
    {
        $page=$request->page;
        $limit=$request->limit;
        $sort=$request->sort;
        $search=$request->search;

        if(!$page){
            $page=1;
        }

        if(!$limit){
            $limit=1000;
        }

        if ($sort=='+id'){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }

        if(!$search){           
            $Questions=Question::where('course_id',$request->course_id)->with('answers')->orderBy('id',$sort)->paginate($limit);    
        }else{
            $Questions=Question::select();
            $Questions=$Questions->where('question','like','%'.$search.'%');
            $Questions=$Questions->where('course_id',$request->course_id)->with('answers')->orderBy('id',$sort)->paginate($limit);
        }
        
       $response = array('status' => true,'message'=>"Question retrieved.",'data'=>$Questions);
        return response()->json($response, 200);
    }

   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestData = $request->only('name', 'description','cover_image','alias','enable_quiz','priority','is_public','tags');
        $validate = Validator::make($requestData, [
            'name' => 'required|max:255',
            'cover_image' => 'image|mimes:jpeg,jpg,png,gif',
        ]);
        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        $alias = str_replace(' ', '-', strtolower($requestData['name']));
        
        $course =  Course::create([
            'name' => $requestData['name'],
            'alias' => $alias,
            'description' => (isset($requestData['description'])) ? $requestData['description'] : '',
            'priority' => (isset($requestData['priority'])) ? $requestData['priority'] : 0,
            'enable_quiz' => (isset($requestData['enable_quiz'])) ? $requestData['enable_quiz'] : 0,
            'is_public' => $requestData['is_public'],
        ]);

        if($request->hasFile('cover_image')){
            $file = $request->file('cover_image');

            $name=$course->id.".".$file->getClientOriginalExtension();
            $course->cover_image='storage/uploads/courses/'.$name;
            $course->save();

            Storage::put(
                'public/uploads/courses/'.$name,
                file_get_contents($request->file('cover_image')->getRealPath())
            );
        }

        $course->tags()->sync($requestData['tags']);

        $course=Course::with('topics')->with('tags')->find($course->id);
        $response = array('status' => true,'message'=>"Courses retrieved.",'data'=>$course);
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Course= Course::with('topics')->with('tags')->find($id);  
        if($Course){
            $response = array('status' => true,'message'=>"Course retrieved.",'data'=>$Course);
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Course not found');
            return response()->json($response, 404);
        }
    }

  

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $course = Course::with('topics','tags')->find($id);

        if(empty($course)){
            $response = array('status' => false,'message'=>'Course not found');
            return response()->json($response, 404);
        }

        $requestData = $request->only('name', 'description','cover_image','alias','enable_quiz','priority','is_public','tags');
        $validate = Validator::make($requestData, [
            'name' => 'required|max:255'
        ]);

        if($validate->fails()){
            $response = array('status' => false,'message'=>'Validation error','data'=>$validate->messages());
            return response()->json($response, 400);
        }

        if (isset($requestData['description'])) {
            $course->description = $requestData['description'];
        }
        
        if (isset($requestData['enable_quiz'])) {
            $course->enable_quiz = $requestData['enable_quiz'];
        }
        if (isset($requestData['priority'])) {
            $course->priority = $requestData['priority'];
        }

        if($request->hasFile('cover_image')){
            $file = $request->file('cover_image');

            $name=$course->id.".".$file->getClientOriginalExtension();
            $course->cover_image='storage/uploads/courses/'.$name;
            $course->save();

            Storage::put(
                'public/uploads/courses/'.$name,
                file_get_contents($request->file('cover_image')->getRealPath())
            );
        }

        $course->name = $requestData['name'];
        $course->alias = $requestData['alias'];
        $course->is_public = $requestData['is_public'];
        $course->save();

        $course->tags()->sync($requestData['tags']);
        
        $course=Course::with('topics')->with('tags')->find($course->id);
        $response = array('status' => true,'message'=>"Course updated.",'data'=>$course);
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $Course= Course::find($id);         
        
         if($Course){
            $Course->topics()->delete();
            $Course->tags()->sync([]);
            $Course->delete(); 
            $response = array('status' => true,'message'=>'Course successfully deleted.');             
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Course not found','data' => array());
            return response()->json($response, 404);
        }
    }

     public function changeCourseStatus(Request $request){
        $Course=Course::find($request->id);

        if($Course){
            $Course->is_active=$request->is_active;
            $Course->save();
            $response = array('status' => true,'message'=>'Course status changed successfully');
            return response()->json($response, 200);
        }else{
            $response = array('status' => false,'message'=>'Course not found');
            return response()->json($response, 400);
        }
    }

    public function uploadCover(){
        $courses=Course::all();
        $file_path=public_path().'/courses/images/';
        foreach ($courses as $course) {
            $filename=$course->cover_image;          
            $project_directory=env('DO_STORE_PATH');
            $file_path=public_path().'/courses/images/'.$filename;


            $store=Storage::disk('spaces')->put($project_directory.'/courses/images/'.$filename, file_get_contents($file_path), 'public');
            
            $url=Storage::disk('spaces')->url($project_directory.'/courses/images/'.$filename);
            
            $cdn_url=str_replace('digitaloceanspaces', 'cdn.digitaloceanspaces', $url);

            $course->cover_image=$cdn_url;
            $course->save();
        }
        
        
    }
}
