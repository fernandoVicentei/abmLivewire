<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    protected $users;
    public $id_user, $name, $lastname, $email, $phone, $status, $password, $modalStatus = false, $modalStatusDelete = false, $modalStatusAnuled = false;
    public $title;
    
    protected $rules = [
        'name' => 'required|min:3|max:191',
        'email' => 'required|email|unique:users,email',
        'lastname' => 'required|min:5|max:191',
        'phone' => 'required|min:6|max:12|unique:users,phone',
        'password' => 'required|min:8|max:12'
    ];

    public function render()
    {
        $this->users = User::orderBy('created_at', 'desc')->paginate(4);
        return view('livewire.users',[
            'users' => $this->users
        ]);
    }

    public function create(){
        $this->title = "Agregar nuevo usuario";
        $this->id_user = 0;
        $this->clearInputs();
        $this->openModal();
    }

    public function store() {
        if($this->id_user == 0) {
            $this->storeUser();
        } else {
            $this->update();
        }
        $this->closeModal();
        $this->clearInputs();
    }

    public function storeUser() {

        $this->validate();

        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->lastname = $this->lastname;
        $user->phone = $this->phone;
        $user->password =  Hash::make($this->password);
        $user->save();
        session()->flash('message', 'El registro fue creado correctamente.');
    }

    public function update() {
        $this->validate([
            'name' => 'required|min:3|max:191',
            'email' => 'required|email|unique:users,email,'.$this->id_user,
            'lastname' => 'required|min:5|max:191',
            'phone' => 'required|min:6|max:12|unique:users,phone,'.$this->id_user
        ]);

        $user = User::findOrFail($this->id_user);
        $user->name = $this->name;
        $user->email = $this->email;
        $user->lastname = $this->lastname;
        $user->phone = $this->phone;
        if( !empty($this->password) ) {
            $user->password =  Hash::make($this->password);
        }
        $user->save();

        session()->flash('message', 'El registro fue actualizado correctamente.');
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        $this->id_user = $id;
        $this->name = $user->name;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->title = "Editar usuario";
        $this->openModal();
    }

    public function delete($id, $isDelete, $statusUser = false) {
        $this->id_user = $id;
        if($isDelete) {
            $this->title = "Eliminar registro de usuario";
            $this->modalStatusDelete = true;
        }else {
            if($statusUser){
                $this->title = "Desea activar el registro de usuario";
            }else {
                $this->title = "Desea desactivar el registro de usuario";
            }
            $this->status = $statusUser;
            $this->modalStatusAnuled = true;
        }
    }

    public function inactiveUser() {
        $user = User::findOrFail($this->id_user);
        $user->status = $this->status;
        $user->save();
        $this->closeModal();

        if($this->status) {
            session()->flash('message', 'El registro fue activado correctamente.');
        }else {
            session()->flash('message', 'El registro fue desactivado correctamente.');
        }

    }

    public function destroy() {
        $user = User::find($this->id_user);
        if ($user) {
            $user->delete();
            session()->flash('message', 'El registro fue eliminado correctamente.');
        } else {
            session()->flash('message', 'El registro no fue encontrado.');
        }
        $this->closeModal();

    }

    public function openModal() {
        $this->modalStatus = true;
    }
    public function closeModal() {
        $this->modalStatus = false;
        $this->modalStatusDelete = false;
        $this->modalStatusAnuled = false;
    }
    public function clearInputs() {
        $this->name = "";
        $this->lastname = "";
        $this->email = "";
        $this->phone = "";
        $this->status = "";
        $this->password = "";
    }

}
