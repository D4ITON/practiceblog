<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserWasCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::allowed()->get();
        return view('admin.users.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = new User;

        $this->authorize('create', $user);

        $roles = Role::with('permissions')->get();
        $permissions = Permission::pluck('name','id');
        return view('admin.users.create',compact('user','roles','permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', new User);
        // validar formulario
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);
        // generar contraseña
        $data['password'] = str_random(8);//cadena de caracteres aleatoria , esto ya se encripta
        // creamos el Usuario
        $user = User::create($data);
        // asignamos los roles
        if ($request->filled('roles')) 
        {
            $user->assignRole($request->roles);
        }
        // asignamos los permisos
        if ($request->filled('permissions')) 
        {
            $user->givePermissionTo($request->permissions);
        }
        // enviamos el email
        // disparalo o despacharlo dispatch
        UserWasCreated::dispatch($user, $data['password']); 
        // regresamos una respuesta al usuario
        return redirect()->route('admin.users.index')->withFlash('El usuario ha sido creado');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('admin.users.show',compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        $this->authorize('update', $user);

       $roles = Role::with('permissions')->get();
       $permissions = Permission::pluck('name','id');
       return view('admin.users.edit',compact('user','roles','permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $user->update($request->validated());
        return back()->withFlash('Usuario actualizado');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}


/*
        // Evento es una clase que transporta informacion, cuando el evento actua el listener tambien, se pueden tener varios listeners para un solo evento,
        // Los listener actuan en respuesta a este evento
        */

/*vid 65 queda*/