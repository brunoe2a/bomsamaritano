<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('busca')) {
            $query->where('name', 'like', "%{$request->busca}%")
                  ->orWhere('email', 'like', "%{$request->busca}%");
        }

        $usuarios = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filtros' => $request->only('busca'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $usuario) // Param bound by resource: usuarios
    {
        if ($usuario->id === 1 && auth()->id() !== 1) { // Proteção básica para o superadmin original
             return redirect()->route('usuarios.index')->with('error', 'Apenas o super administrador pode editar sua própria conta principal.');
        }

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->load('roles'),
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        if ($usuario->id === 1 && auth()->id() !== 1) {
             return redirect()->route('usuarios.index')->with('error', 'Sem permissão para editar conta superadmin');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }

        $usuario->save();

        if ($usuario->id !== 1) { // Evita tirar admin do admin supremo sem querer
             $usuario->syncRoles([$validated['role']]);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === 1) {
             return redirect()->route('usuarios.index')->with('error', 'O usuário admin principal nunca pode ser deletado.');
        }
        
        if ($usuario->id === auth()->id()) {
             return redirect()->route('usuarios.index')->with('error', 'Você não pode excluir a sua própria conta! Peça para outro administrador fazê-lo.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}
