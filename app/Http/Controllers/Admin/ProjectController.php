<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;
use App\User;
use App\Project;
use App\ItemPermission;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

use App\Services\Permissions\ModelPermission;

class ProjectController extends Controller
{
    private $check;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:project-list');
        // $this->middleware('permission:project-create', ['only' => ['create','store']]);
        // $this->middleware('permission:project-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:project-delete', ['only' => ['destroy']]);

        $this->check = new ModelPermission();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $projects = Project::latest()->paginate(5);
        return view('admin.projects.index',compact('projects'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.projects.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'detail' => 'required',
        ]);

        $project = Project::create($request->all());

        $permissionNameShow = 'project-show-'.$project->id;
        $permissionNameEdit = 'project-edit-'.$project->id;
        $permissionNameDelete = 'project-delete-'.$project->id;

        ItemPermission::create([
            'type' => 'show',
            'name' => $permissionNameShow,
            'model_name' => 'App/Project',
            'model_id' => $project->id
        ]);

        ItemPermission::create([
            'type' => 'edit',
            'name' => $permissionNameEdit,
            'model_name' => 'App/Project',
            'model_id' => $project->id
        ]);

        ItemPermission::create([
            'type' => 'delete',
            'name' => $permissionNameDelete,
            'model_name' => 'App/Project',
            'model_id' => $project->id
        ]);


        return redirect()->route('admin.projects.index')
            ->with('success','Project created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $showOptions = array('type' => 'show', 'model_name' => 'App/Project', 'model_id' => $project->id);
        $this->check->handle('project-list', $showOptions);
        $usersWithRoles = $this->check->authUsers('App/Project', $project->id);

        dump($usersWithRoles);

        return view('admin.projects.show',compact('project'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $editOptions = array('type' => 'edit', 'model_name' => 'App/Project', 'model_id' => $project->id);
        $this->check->handle('project-edit', $editOptions);

        return view('admin.projects.edit',compact('project'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $editOptions = array('type' => 'edit', 'model_name' => 'App/Project', 'model_id' => $project->id);
        $this->check->handle('project-edit', $editOptions);

        request()->validate([
            'name' => 'required',
            'detail' => 'required',
        ]);


        $project->update($request->all());


        return redirect()->route('admin.projects.index')
            ->with('success','Project updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $deleteOptions = array('type' => 'delete', 'model_name' => 'App/Project', 'model_id' => $project->id);
        $this->check->handle('project-delete', $deleteOptions);

        $project->delete();
        ItemPermission::where(['model_id' => $project->id, 'model_name' => 'App/Project'])->delete();

        return redirect()->route('admin.projects.index')
            ->with('success','Project deleted successfully');
    }
}
