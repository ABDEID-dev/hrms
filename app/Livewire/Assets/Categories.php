<?php

namespace App\Livewire\Assets;

use App\Models\Category;
use App\Models\SubCategory;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;

    // 👉 Variables
    public $search_term_categories = null;

    public $search_term_sub_categories = null;

    public $category;

    public $categoryInfo;

    public $subCategory;

    public $categoryName;

    public $subCategoryCategoryId;

    public $subCategoryName;

    public $isEdit = false;

    public $confirmedCategoryId;

    public $confirmedSubCategoryId;

    public function mount()
    {
        $this->categoryInfo = Category::with('subCategory')->first();
    }

    public function render()
    {
        $categories = Category::where('id', 'like', '%'.$this->search_term_categories.'%')
            ->orWhere('name', 'like', '%'.$this->search_term_categories.'%')
            ->paginate(6);

        $subCategories = SubCategory::where('id', 'like', '%'.$this->search_term_sub_categories.'%')
            ->orWhere('name', 'like', '%'.$this->search_term_sub_categories.'%')
            ->paginate(6);

        return view('livewire.assets.categories', [
            'allCategories' => Category::orderBy('name')->get(),
            'categories' => $categories,
            'subCategories' => $subCategories,
        ]);
    }

    public function showCategoryInfo($categoryId)
    {
        $this->categoryInfo = Category::with('subCategory')->find($categoryId);
    }

    public function submitCategory()
    {
        $this->isEdit ? $this->editCategory() : $this->addCategory();
    }

    public function showNewCategoryModal()
    {
        $this->reset('isEdit', 'categoryName');
    }

    public function addCategory()
    {
        // $this->validate();
        Category::create([
            'name' => $this->categoryName,
        ]);

        $this->dispatch('closeModal', elementId: '#categoryModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    public function showEditCategoryModal(Category $category)
    {
        $this->reset('isEdit', 'categoryName');
        $this->isEdit = true;
        $this->category = $category;
        $this->categoryName = $category->name;
    }

    public function editCategory()
    {
        // $this->validate();
        $this->category->update([
            'name' => $this->categoryName,
        ]);

        $this->dispatch('closeModal', elementId: '#categoryModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        $this->reset('isEdit', 'categoryName');
    }

    public function confirmDeleteCategory($id)
    {
        $this->confirmedCategoryId = $id;
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    public function submitSubCategory()
    {
        $this->isEdit ? $this->editSubCategory() : $this->addSubCategory();
    }

    public function showNewSubCategoryModal()
    {
        $this->reset('isEdit', 'subCategoryName', 'subCategoryCategoryId');
    }

    public function addSubCategory()
    {
        $this->validate([
            'subCategoryName' => ['required', 'string'],
            'subCategoryCategoryId' => ['required', 'exists:categories,id'],
        ]);

        SubCategory::create([
            'category_id' => $this->subCategoryCategoryId,
            'name' => $this->subCategoryName,
        ]);

        $this->dispatch('closeModal', elementId: '#subCategoryModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
        $this->reset('subCategoryName', 'subCategoryCategoryId');
    }

    public function showEditSubCategoryModal(SubCategory $subCategory)
    {
        $this->reset('isEdit', 'subCategoryName', 'subCategoryCategoryId');
        $this->isEdit = true;
        $this->subCategory = $subCategory;
        $this->subCategoryName = $subCategory->name;
        $this->subCategoryCategoryId = $subCategory->category_id;
    }

    public function editSubCategory()
    {
        $this->validate([
            'subCategoryName' => ['required', 'string'],
            'subCategoryCategoryId' => ['required', 'exists:categories,id'],
        ]);

        $this->subCategory->update([
            'category_id' => $this->subCategoryCategoryId,
            'name' => $this->subCategoryName,
        ]);

        $this->dispatch('closeModal', elementId: '#subCategoryModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        $this->reset('isEdit', 'subCategoryName', 'subCategoryCategoryId');
    }

    public function confirmDeleteSubCategory($id)
    {
        $this->confirmedSubCategoryId = $id;
    }

    public function deleteSubCategory(SubCategory $subCategory)
    {
        $subCategory->delete();
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }
}
