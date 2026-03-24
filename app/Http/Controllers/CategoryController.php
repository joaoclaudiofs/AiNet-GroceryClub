<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\CategoryFormRequest;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $searchByName = $request->get('search-name');
        $orderBy = $request->get('order-by') ?? 'name';
        $direction = $request->get('direction') ?? 'asc';

        $categoriesQuery = Category::query();

        if ($searchByName) {
            $categoriesQuery->where('name', 'like', "%$searchByName%");
        }

        $categories = $categoriesQuery->orderBy($orderBy, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories', 'searchByName', 'orderBy', 'direction'));
    }

    public function create(): View
    {
        return view('categories.create')->with('category', new Category());
    }

    public function store(CategoryFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $category = DB::transaction(function () use ($validated) {
            $category = new Category();
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? null;
            $category->save();
            return $category;
        });

        return redirect()->route('categories.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Category <strong>{$category->name}</strong> created successfully!");
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryFormRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $category) {
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? null;
            $category->save();
        });

        return redirect()->route('categories.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Category <strong>{$category->name}</strong> updated successfully!");
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            DB::transaction(function () use ($category) {
                $category->delete();
            });

            return redirect()->route('categories.index')
                ->with('alert-type', 'success')
                ->with('alert-msg', "Category <strong>{$category->name}</strong> deleted successfully!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('alert-type', 'danger')
                ->with('alert-msg', "Failed to delete category <strong>{$category->name}</strong>.");
        }
    }

    public function show(Category $category): View
    {
        return view('categories.show', compact('category'));
    }
}
