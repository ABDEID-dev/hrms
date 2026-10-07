@push('custom-css')

@endpush

<div wire:ignore.self class="modal fade" id="categoryInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-simple">
    <div class="card">
      <h5 class="card-header">Treeview</h5>
      <div class="card-body">
        @if ($categoryInfo)
          <div id="jstree-basic" class="jstree jstree-1 jstree-default-dark" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j1_1" aria-busy="false">
            <ul class="jstree-container-ul jstree-children" role="group">
              <li role="none" data-jstree="{&quot;icon&quot; : &quot;ti ti-folder&quot;}" id="j1_1" class="jstree-node jstree-open">
                <i class="jstree-icon jstree-ocl" role="presentation"></i>
                <a class="jstree-anchor" href="#" tabindex="-1" role="treeitem" aria-selected="false" aria-level="1" aria-expanded="true" id="j1_1_anchor">
                  <i class="jstree-icon jstree-themeicon ti ti-folder jstree-themeicon-custom" role="presentation"></i>
                  {{ $categoryInfo->name . ' (' . $categoryInfo->subCategory->count() . ')' }}
                </a>
                <ul role="group" class="jstree-children">
                  @forelse ($categoryInfo->subCategory as $subCategory)
                    <li role="none" data-jstree="{&quot;icon&quot; : &quot;ti ti-folder&quot;}" id="j1_5" class="jstree-node jstree-leaf">
                      <i class="jstree-icon jstree-ocl" role="presentation"></i>
                      <a class="jstree-anchor" href="#" tabindex="-1" role="treeitem" aria-selected="false" aria-level="2" id="j1_5_anchor">
                        <i class="jstree-icon jstree-themeicon ti ti-folder jstree-themeicon-custom" role="presentation"></i>
                        {{ $subCategory->name }}
                      </a>
                    </li>
                  @empty
                    <li class="jstree-node jstree-leaf text-muted px-4 py-2">{{ __('No sub-categories found.') }}</li>
                  @endforelse
                </ul>
              </li>
            </ul>
          </div>
        @else
          <p class="text-muted mb-0">{{ __('No category selected.') }}</p>
        @endif
      </div>
    </div>
  </div>
</div>

@push('custom-scripts')

@endpush
