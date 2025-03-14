<?php

namespace App\Http\Controllers;

use App\Models\Common;
use App\Models\Commercial;
use App\Models\Deal;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title_site = "Понель администратора | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

        // Получение данных
        $usersCount = User::count();
        $commonsCount = Common::count();
        $commercialsCount = Commercial::count();
        $dealsCount = Deal::count();
        $estimatesCount = Estimate::count();

        // Получение списка пользователей
        $users = User::all();

        return view('admin', compact(
            'user',
            'title_site',
            'usersCount',
            'commonsCount',
            'commercialsCount',
            'dealsCount',
            'estimatesCount',
            'users'
        ));
    }
   

    public function user_admin()
    {
        $title_site = "Управление пользователями | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
    
        // Получение данных
        $usersCount = User::count();
        $commonsCount = Common::count();
        $commercialsCount = Commercial::count();
        $dealsCount = Deal::count();
        $estimatesCount = Estimate::count();
    
        // Получение списка пользователей
        $users = User::all();
    
        return view('admin.users', compact(
            'user',
            'title_site',
            'usersCount',
            'commonsCount',
            'commercialsCount',
            'dealsCount',
            'estimatesCount',
            'users'
        ));
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Проверяем, был ли передан новый пароль
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password); // Хешируем пароль перед сохранением
        }
    
        // Обновляем остальные данные пользователя
        $user->update($request->only(['name', 'phone', 'status']));
    
        return response()->json(['success' => true]);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Удаление пользователя
        $user->delete();

        return response()->json(['success' => true]);
    }
    public function userBriefs($id)
    {
        $user = User::findOrFail($id);
        $title_site = "Управление брифами пользователя | Личный кабинет Экспресс-дизайн";
        // Получаем брифы пользователя
        $commonBriefs = Common::where('user_id', $id)->get();
        $commercialBriefs = Commercial::where('user_id', $id)->get();

        return view('admin.user_briefs', compact('user', 'commonBriefs', 'commercialBriefs', 'title_site'));
    }
    public function edit($id)
    {
        $title_site = "Понель администратора | Личный кабинет Экспресс-дизайн";
        $brif = Commercial::findOrFail($id); // или Common::findOrFail($id), если общий бриф
        $zones = $brif->zones ? json_decode($brif->zones, true) : [];
        $preferences = $brif->preferences ? json_decode($brif->preferences, true) : [];
        $user = $brif->user;

        return view('admin.brief_edit', compact('Бриф прикриплен', 'title_site', 'zones', 'preferences', 'user'));
    }

    public function editCommonBrief($id)
    {
        $title_site = "Редактировать общий бриф | Личный кабинет Экспресс-дизайн";
        $brief = Common::findOrFail($id);  // Get the Common brief by ID
        $zones = $brief->zones ? json_decode($brief->zones, true) : [];  // Decode zones if available
        $preferences = $brief->preferences ? json_decode($brief->preferences, true) : [];  // Decode preferences if available
        $user = $brief->user;  // Get the user who created the brief
     
        return view('admin.brief_edit_common', compact('brief', 'title_site', 'zones', 'preferences', 'user'));
    }
    
    public function updateCommonBrief(Request $request, $id)
    {
        $brief = Common::findOrFail($id);  // Get the brief by ID
    
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'status' => 'required|string|in:Активный,Завершенный,completed',  // Example status validation
            'zones' => 'nullable|array',
            'preferences' => 'nullable|array',
        ]);
    
        // Update the basic details of the brief
        $brief->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'status' => $request->input('status'),
        ]);
    
        // If zones or preferences were submitted, update them
        if ($request->has('zones')) {
            $brief->zones = json_encode($request->input('zones'));
        }
    
        if ($request->has('preferences')) {
            $brief->preferences = json_encode($request->input('preferences'));
        }
    
        // Save the changes
        $brief->save();
    
        return response()->json(['success' => true]);
    }
        // Controller for handling Commercial Briefs

// Edit method to load the brief data for editing
public function editCommercialBrief($id)
{
    $title_site = "Редактировать коммерческий бриф | Личный кабинет Экспресс-дизайн";
    $brief = Commercial::findOrFail($id);  // Find the commercial brief by ID

    // Decode the questions or zones (depending on your structure)
    $questions = json_decode($brief->questions, true) ?? [];
    $preferences = json_decode($brief->preferences, true) ?? [];
    $zones = json_decode($brief->zones, true) ?? [];

    return view('admin.brief_edit_commercial', compact('brief', 'title_site', 'questions', 'preferences', 'zones'));
}


public function updateCommercialBrief(Request $request, $id)
{
    $brief = Commercial::findOrFail($id);  // Find the commercial brief by ID

    // Validate the input data (you can adjust the validation rules as per your needs)
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric',
        'status' => 'required|string|in:Активный,Завершенный,completed',
        'questions' => 'nullable|array',  // Validate questions if they exist
    ]);

    // Update the commercial brief
    $brief->update([
        'title' => $data['title'],
        'description' => $data['description'],
        'price' => $data['price'],
        'status' => $data['status'],
    ]);

    // If questions were provided, update them
    if ($request->has('questions')) {
        $brief->questions = json_encode($request->input('questions'));
    }

    // If preferences were provided, update them
    if ($request->has('preferences')) {
        $brief->preferences = json_encode($request->input('preferences'));
    }

    // If zones were provided, update them
    if ($request->has('zones')) {
        $brief->zones = json_encode($request->input('zones'));
    }

    $brief->save();  // Save the updated brief

    return redirect()->route('admin.briefs.index')->with('success', 'Бриф успешно обновлен.');
}

}
