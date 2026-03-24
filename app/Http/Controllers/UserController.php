<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UserFormRequest;
use Illuminate\Support\Facades\DB;
use App\Traits\UserPhotoFileStorage;
use function PHPUnit\Framework\isArray;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use UserPhotoFileStorage;

    public function members(Request $request): View
    {
        $membersQuery = User::query()->whereIn('type', ['member', 'pending_member']);

        $filterByGender = $request->get('gender');
        if ($filterByGender && is_array($filterByGender)) {
            $membersQuery->whereIn('users.gender', $filterByGender);
        } else $filterByGender = null;

        $filterByStatus = $request->get('status');
        if ($filterByStatus && is_array($filterByStatus)) {
            $membersQuery->where(function (Builder $query) use ($filterByStatus) {
                if (in_array('active', $filterByStatus)) {
                    $query->orWhere(function (Builder $query) {
                        $query->where('users.blocked', '=', 0)
                            ->where('users.type', '=', 'member');
                    });
                }
                if (in_array('pending', $filterByStatus)) {
                    $query->orWhere('users.type', '=', 'pending_member');
                }
                if (in_array('blocked', $filterByStatus)) {
                    $query->orWhere('users.blocked', '=', 1);
                }
            });
        } else $filterByStatus = null;

        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'desc';
        if ($orderByElement) {
            if ($orderByElement === 'card') {
                $membersQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->orderBy('cards.card_number', $orderByDirection);
            } else {
                $membersQuery->orderBy($orderByElement, $orderByDirection)
                    ->orderBy('users.created_at', $orderByDirection);
            }
        } else {
            $membersQuery->orderBy('users.created_at', $orderByDirection);
        }

        $search = $request->get('search');
        $searchWithRegex = $request->get('search-with-regex');
        if ($search) {
            if ($searchWithRegex) {
                $membersQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('users.name', 'regexp', $search)
                            ->orWhere('users.email', 'regexp', $search)
                            ->orWhere('users.nif', 'regexp', $search)
                            ->orWhere('cards.card_number', 'regexp', $search);
                    });
            } else {
                $membersQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('users.name', 'like', "%$search%")
                            ->orWhere('users.email', 'like', "%$search%")
                            ->orWhere('users.nif', 'like', "%$search%")
                            ->orWhere('cards.card_number', 'like', "%$search%");
                    });
            }
        }

        try {
            $members = $membersQuery
                ->with('card')
                ->paginate(20)
                ->withQueryString();
        } catch (\Exception $error) {
            $members = new LengthAwarePaginator([], 0, 1);
        }

        return view('users.members.index')
            ->with('members', $members)
            ->with(compact('filterByGender', 'filterByStatus', 'search', 'searchWithRegex', 'orderByElement', 'orderByDirection'));
    }

    public function employees(Request $request): View
    {
        $employeesQuery = User::query()->whereIn('type', ['employee']);

        $filterByGender = $request->get('gender');
        if ($filterByGender && is_array($filterByGender)) {
            $employeesQuery->whereIn('users.gender', $filterByGender);
        } else $filterByGender = null;

        $filterByStatus = $request->get('status');
        if ($filterByStatus && is_array($filterByStatus)) {
            $employeesQuery->where(function (Builder $query) use ($filterByStatus) {
                if (in_array('active', $filterByStatus)) {
                     $query->orWhere('users.blocked', '=', 0);
                }
                if (in_array('blocked', $filterByStatus)) {
                    $query->orWhere('users.blocked', '=', 1);
                }
            });
        } else $filterByStatus = null;

        $search = $request->get('search');
        $searchWithRegex = $request->get('search-with-regex');
        if ($search) {
            if ($searchWithRegex) {
                $employeesQuery->where(function (Builder $query) use ($search) {
                    $query->where('users.name', 'regexp', $search)
                        ->orWhere('users.email', 'regexp', $search);
                });

            } else {
                $employeesQuery->where(function (Builder $query) use ($search) {
                    $query->where('users.name', 'like', "%$search%")
                        ->orWhere('users.email', 'like', "%$search%");
                });
            }
        }

        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'desc';
        if ($orderByElement) {
            $employeesQuery->orderBy($orderByElement, $orderByDirection)
                ->orderBy('users.created_at', $orderByDirection);
        } else {
            $employeesQuery->orderBy('users.created_at', $orderByDirection);
        }

        try {
            $employees = $employeesQuery
                ->paginate(20)
                ->withQueryString();
        } catch (\Exception $error) {
            $employees = new LengthAwarePaginator([], 0, 1);
        }

        return view('users.employees.index')
            ->with('employees', $employees)
            ->with(compact('filterByGender', 'filterByStatus', 'search', 'searchWithRegex', 'orderByElement', 'orderByDirection'));
    }

    public function boards(Request $request): View
    {
        $boardsQuery = User::query()->whereIn('type', ['board']);


        if($request->get('order-element') == 'best'){
              $boardWithMoreOrders =  User::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();
            $boardsQuery->whereIn('users.id', $boardWithMoreOrders->pluck('id'));
        }

        // if($request->get('order-element') == 'best'){
        //       $boardWithMoreOrders =  User::whereIn('type', ['board'])->withCount('orders')
        //     ->orderBy('orders_count', 'desc')
        //     ->limit(5)
        //     ->get();
        //     $boardsQuery->whereIn('users.id', $boardWithMoreOrders->pluck('id'));
        // }
        // $boardWithMoreOrders =  User::withCount('orders')
        //     ->orderBy('orders_count', 'desc')
        //     ->first();

        // $boardsQuery->where('users.id', $boardWithMoreOrders->id);

        $filterByGender = $request->get('gender');
        if ($filterByGender && is_array($filterByGender)) {
            $boardsQuery->whereIn('users.gender', $filterByGender);
        } else $filterByGender = null;

        $filterByStatus = $request->get('status');
        if ($filterByStatus && is_array($filterByStatus)) {
            $boardsQuery->where(function (Builder $query) use ($filterByStatus) {
                if (in_array('active', $filterByStatus)) {
                     $query->orWhere('users.blocked', '=', 0);
                }
                if (in_array('blocked', $filterByStatus)) {
                    $query->orWhere('users.blocked', '=', 1);
                }
            });
        } else $filterByStatus = null;


        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'desc';
        if ($orderByElement && $orderByElement !== 'best') {
            if ($orderByElement === 'card') {
                $boardsQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->orderBy('cards.card_number', $orderByDirection);
            } else {
                $boardsQuery->orderBy($orderByElement, $orderByDirection)
                    ->orderBy('users.created_at', $orderByDirection);
            }
        } else {
            $boardsQuery->orderBy('users.created_at', $orderByDirection);
        }

        $search = $request->get('search');
        $searchWithRegex = $request->get('search-with-regex');
        if ($search) {
            if ($searchWithRegex) {
                $boardsQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('users.name', 'regexp', $search)
                            ->orWhere('users.email', 'regexp', $search)
                            ->orWhere('users.nif', 'regexp', $search)
                            ->orWhere('cards.card_number', 'regexp', $search);
                    });

            } else {
                $boardsQuery->join('cards', 'users.id', '=', 'cards.id')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('users.name', 'like', "%$search%")
                            ->orWhere('users.email', 'like', "%$search%")
                            ->orWhere('users.nif', 'like', "%$search%")
                            ->orWhere('cards.card_number', 'like', "%$search%");
                    });
            }
        }

        try {
            $boards = $boardsQuery
                ->with('card')
                ->paginate(20)
                ->withQueryString();
        } catch (\Exception $error) {
            $boards = new LengthAwarePaginator([], 0, 1);
        }

        return view('users.boards.index')
            ->with('boards', $boards)
            ->with(compact('filterByGender', 'filterByStatus', 'search', 'searchWithRegex', 'orderByElement', 'orderByDirection'));
    }

    public function registerEmployee(): View
    {
        $employee = new User();
        return view('users.employees.register')->with('employee', $employee);
    }

    public function saveEmployee(UserFormRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        DB::transaction(function () use ($validatedData, $request) {
            $newEmployee = new User();
            $newEmployee->name = $validatedData['name'];
            $newEmployee->email = $validatedData['email'];
            $newEmployee->gender = $validatedData['gender'];
            $newEmployee->password = $validatedData['password'];
            $newEmployee->type = "employee";
            $newEmployee->save();
            $this->storeUserPhoto($request->photo, $newEmployee);
            return $newEmployee;
        });

        return redirect()->route('users.employees')
            ->with('alert-type', 'success');
    }

    public function view(User $user): View
    {
        return view('users.view')->with('user', $user);
    }

    public function edit(User $user): View
    {
        return view('users.edit')->with('user', $user);
    }

    public function update(UserFormRequest $request, User $user): RedirectResponse
    {
        $validatedData = $request->validated();

        DB::transaction(function () use ($user, $validatedData, $request) {
            $user->name                         = $validatedData['name'];
            $user->email                        = $validatedData['email'];
            $user->gender                       = $validatedData['gender'];
            $user->nif                          = $validatedData['nif'];
            $user->default_delivery_address     = $validatedData['default_delivery_address'];
            $user->default_payment_type         = $validatedData['default_payment_type'];
            $user->default_payment_reference    = $validatedData['default_payment_reference'];
            $user->save();
            $this->storeUserPhoto($request->photo, $user);
            return $user;
        });

        $route = 'users.' . $user->type . 's';

        return redirect()->route($route)
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been updated successfully!');
    }

    public function block(User $user): RedirectResponse
    {
        $user->blocked = true;
        $user->save();

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been blocked successfully!');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->blocked = false;
        $user->save();

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been unblocked successfully!');
    }

    public function promote(User $user): RedirectResponse
    {
        $user->type = 'board';
        $user->save();

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been promoted successfully!');
    }

    public function demote(User $user): RedirectResponse
    {
        $user->type = 'member';
        $user->save();

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been promoted successfully!');
    }

    public function delete(User $user): RedirectResponse
    {
        if ($user == Auth::user()) {
            return redirect()->back()
                ->with('alert-type', 'danger')
                ->with('alert-msg', 'You cant delete yourself.');
        }

        if ($user->card) {
            $user->card->delete();
        }

        $user->delete();

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'User has been deleted successfully!');
    }
}
