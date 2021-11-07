<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = Role::with(['users' => function ($q) {
        //     $q->select('role_id', 'name', 'email');
        // }])->get();
        $data = Role::all();
        return $this->responseSuccess('List all roles', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'role_name' => 'required|string|unique:roles,role_name',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $role = Role::create([
            'role_name' => $input['role_name'],
        ]);

        $data = [
            'role' => $role,
        ];

        return $this->responseSuccess('Role created successfully', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $role = Role::where('id', $id)->first();
        if (!$role) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'role_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error validation', $validator->errors(), 400);
        }

        $role->update($input);

        $data = Role::find($id);

        return $this->responseSuccess('Role updated successfully', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id' ,$id)->first();
        if (!$user) return $this->responseFailed('Role not found', '', 404);

        $user->delete();

        return $this->responseSuccess('Role deleted successfully');
    }
}
