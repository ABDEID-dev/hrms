<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('ui.inventory'))

  @section('page-style')
    <style>
      .inventory-page .amount-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .inventory-page .summary-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .85rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .inventory-page .summary-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.1rem;
      }

      .inventory-page .category-summary-grid {
        border-top: 1px solid rgba(var(--bs-border-color-rgb), .45);
        margin-top: 1rem;
        padding-top: 1rem;
      }

      .inventory-page .category-summary-title {
        font-weight: 700;
      }

      .inventory-page .category-summary-line {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .85rem;
      }

      .inventory-page .sheet-table > thead > tr > th {
        background-color: #264b12 !important;
        color: #fff !important;
        vertical-align: middle;
        white-space: nowrap;
      }

      .inventory-page .actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
      }

      .inventory-page .product-image-preview,
      .inventory-page .product-image-placeholder {
        width: 64px;
        height: 64px;
        flex: 0 0 64px;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .35rem;
      }

      .inventory-page .product-image-preview {
        object-fit: contain;
        background: var(--bs-tertiary-bg);
      }

      .inventory-page .product-image-placeholder {
        display: grid;
        place-items: center;
        color: var(--bs-secondary-color);
        background: rgba(var(--bs-body-bg-rgb), .45);
        font-size: 1.35rem;
      }

      .inventory-page .product-image-trigger {
        display: inline-flex;
        padding: 0;
        border: 0;
        border-radius: .35rem;
        background: transparent;
        cursor: zoom-in;
      }

      .inventory-page .product-image-trigger:focus-visible {
        outline: 2px solid var(--bs-primary);
        outline-offset: 3px;
      }

      .inventory-page .product-image-upload-panel {
        padding: .7rem .85rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-inline-start: 3px solid var(--bs-success);
        border-radius: .45rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .inventory-page .product-image-upload-heading {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
      }

      .inventory-page .product-image-upload-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: .35rem;
        color: var(--bs-success);
        background: rgba(var(--bs-success-rgb), .12);
        font-size: 1.1rem;
      }

      .inventory-page .product-image-upload-copy {
        flex: 1 1 auto;
        min-width: 0;
      }

      .inventory-page .product-image-upload-preview {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-top: .6rem;
        padding: .45rem .55rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .35rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .inventory-page .product-image-upload-preview img {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        object-fit: contain;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .25rem;
        background: var(--bs-tertiary-bg);
      }

      .inventory-page .product-image-upload-preview-copy {
        min-width: 0;
        overflow-wrap: anywhere;
      }

      .inventory-page .product-image-upload-preview {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-top: .85rem;
        padding: .65rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .4rem;
        background: rgba(var(--bs-body-bg-rgb), .5);
      }

      .inventory-page .product-image-upload-preview img {
        width: 68px;
        height: 68px;
        flex: 0 0 68px;
        object-fit: contain;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .3rem;
        background: var(--bs-tertiary-bg);
      }

      .inventory-page .inventory-image-preview-dialog {
        max-width: min(92vw, 680px);
      }

      .inventory-page .inventory-image-stage {
        display: grid;
        width: 100%;
        aspect-ratio: 1 / 1;
        place-items: center;
        overflow: hidden;
        border-radius: .4rem;
        background: var(--bs-tertiary-bg);
      }

      .inventory-page .inventory-image-stage img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
      }

      .inventory-page .inventory-image-preview-actions {
        display: flex;
        justify-content: center;
        gap: .75rem;
        margin-top: 1rem;
      }

      @media (max-width: 767.98px) {
        .inventory-page {
          margin-inline: -.75rem;
        }

        .inventory-page .card {
          border-radius: 0;
        }

        .inventory-page .table {
          min-width: 980px;
        }
      }

      @media (max-width: 575.98px) {
        .inventory-page .product-image-upload-panel {
          padding: .65rem;
        }

        .inventory-page .product-image-upload-heading {
          flex-wrap: wrap;
        }

        .inventory-page .product-image-upload-copy {
          flex-basis: calc(100% - 3.5rem);
        }

        .inventory-page .product-image-upload-heading > .btn {
          margin-inline-start: 2.6rem;
        }

        .inventory-page .inventory-image-preview-dialog {
          max-width: calc(100vw - 1rem);
          margin: .5rem auto;
        }

        .inventory-page .inventory-image-stage {
          max-height: calc(100dvh - 190px);
        }
      }
    </style>
  @endsection

  <div class="inventory-page">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-0">{{ __('ui.inventory') }}</h5>
          <small class="text-muted">{{ __('ui.inventory_hint') }}</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <select wire:model.live="account" class="form-select">
            @foreach($accountNames as $key => $name)
              <option value="{{ $key }}">{{ __('ui.inventory_account_'.$key) }}</option>
            @endforeach
          </select>
          <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('ui.search_inventory') }}">
          @if($this->canAddInventoryProduct())
            <button wire:click="showNewProductModal" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryProductModal">
              <i class="ti ti-plus me-1"></i>
              {{ __('ui.add_product') }}
            </button>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-2 col-md-4">
            <div class="summary-box">
              <div class="text-muted small mb-1">{{ __('ui.products_count') }}</div>
              <div class="summary-value">{{ number_format((float) ($summary->products_count ?? 0), 0) }}</div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4">
            <div class="summary-box">
              <div class="text-muted small mb-1">{{ __('ui.pieces_in_stock') }}</div>
              <div class="summary-value">{{ number_format((float) ($summary->pieces_stock ?? 0), 0) }}</div>
            </div>
          </div>
          <div class="col-lg-2 col-md-4">
            <div class="summary-box">
              <div class="text-muted small mb-1">{{ __('ui.pieces_sold') }}</div>
              <div class="summary-value text-success">{{ number_format((float) ($summary->pieces_sold ?? 0), 0) }}</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-box">
              <div class="text-muted small mb-1">{{ __('ui.grams_in_stock') }}</div>
              <div class="summary-value">{{ $this->formatQuantity($summary->grams_stock ?? 0, 'gram') }} g</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-box">
              <div class="text-muted small mb-1">{{ __('ui.grams_sold') }}</div>
              <div class="summary-value text-success">{{ $this->formatQuantity($summary->grams_sold ?? 0, 'gram') }} g</div>
            </div>
          </div>
        </div>

        <div class="category-summary-grid">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h6 class="mb-0">{{ __('ui.category_statistics') }}</h6>
            <small class="text-muted">{{ __('ui.category_statistics_hint') }}</small>
          </div>
          <div class="row g-3">
            @foreach($categorySummaries as $categorySummary)
              <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="summary-box h-100">
                  <div class="category-summary-title mb-2">{{ $categorySummary['label'] }}</div>
                  <div class="category-summary-line text-muted">
                    <span>{{ __('ui.products_count') }}</span>
                    <span class="amount-cell">{{ number_format($categorySummary['products_count'], 0) }}</span>
                  </div>
                  <div class="category-summary-line">
                    <span>{{ __('ui.in_stock') }}</span>
                    <span class="amount-cell">{{ $this->formatQuantity($categorySummary['stock_quantity'], $categorySummary['unit']) }} {{ $this->unitShortLabel($categorySummary['unit']) }}</span>
                  </div>
                  <div class="category-summary-line text-success">
                    <span>{{ __('ui.sold') }}</span>
                    <span class="amount-cell">{{ $this->formatQuantity($categorySummary['sold_quantity'], $categorySummary['unit']) }} {{ $this->unitShortLabel($categorySummary['unit']) }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="table-responsive">
        <table class="table sheet-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('ui.product') }}</th>
              <th class="text-center">{{ __('ui.category') }}</th>
              <th class="text-center">{{ __('ui.sku') }}</th>
              <th class="text-center">{{ __('ui.color') }}</th>
              <th class="text-center">{{ __('ui.length') }}</th>
              <th class="text-center">{{ __('ui.unit') }}</th>
              <th class="text-center">{{ __('ui.in_stock') }}</th>
              <th class="text-center">{{ __('ui.sold') }}</th>
              <th class="text-center">{{ __('ui.price') }}</th>
              <th class="text-center">{{ __('ui.status') }}</th>
              <th class="text-center">{{ __('ui.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $product)
              @php($isLow = $product->low_stock_threshold !== null && (float) $product->stock_quantity <= (float) $product->low_stock_threshold)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    @if($this->productImageUrl($product))
                      <button
                        type="button"
                        class="product-image-trigger"
                        data-bs-toggle="modal"
                        data-bs-target="#inventoryImagePreviewModal"
                        data-image-url="{{ $this->productImageUrl($product) }}"
                        data-product-name="{{ $product->name }}"
                        title="عرض صورة {{ $product->name }}"
                        aria-label="عرض صورة {{ $product->name }}"
                      >
                        <img src="{{ $this->productImageUrl($product) }}" alt="{{ $product->name }}" class="product-image-preview" loading="lazy">
                      </button>
                    @else
                      <span class="product-image-placeholder" aria-hidden="true"><i class="ti ti-photo"></i></span>
                    @endif
                    <div>
                      <div class="fw-semibold">{{ $product->name }}</div>
                      @if($product->note)
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($product->note, 55) }}</small>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="text-center">{{ $this->categoryLabel($product->category) }}</td>
                <td class="text-center">{{ $product->sku ?: '---' }}</td>
                <td class="text-center">{{ $product->color ?: '---' }}</td>
                <td class="text-center">{{ $product->length_cm ? $product->length_cm.' cm' : '---' }}</td>
                <td class="text-center">{{ $product->unit === 'gram' ? __('ui.gram') : __('ui.piece') }}</td>
                <td class="amount-cell">
                  <span class="{{ $isLow ? 'text-danger fw-semibold' : '' }}">
                    {{ $this->formatQuantity($product->stock_quantity, $product->unit) }}
                  </span>
                </td>
                <td class="amount-cell">{{ $this->formatQuantity($product->sold_quantity, $product->unit) }}</td>
                <td class="amount-cell">{{ $product->unit_price !== null ? 'AED '.number_format((float) $product->unit_price, 2) : '---' }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $product->is_active ? 'success' : 'secondary' }}">
                    {{ $product->is_active ? __('ui.active') : __('ui.inactive') }}
                  </span>
                  @if($isLow)
                    <span class="badge bg-label-danger">{{ __('ui.low_stock') }}</span>
                  @endif
                </td>
                <td class="text-center">
                  <div class="actions-wrap">
                    @if($this->canAddInventoryStock())
                      <button wire:click="showStockModal({{ $product->id }})" type="button" class="btn btn-sm btn-icon btn-label-success" data-bs-toggle="modal" data-bs-target="#inventoryStockModal" title="Stock">
                        <i class="ti ti-stack-push"></i>
                      </button>
                    @endif
                    @if($this->canManageInventory())
                      <button wire:click="showEditProductModal({{ $product->id }})" type="button" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="modal" data-bs-target="#inventoryProductModal" title="Edit">
                        <i class="ti ti-pencil"></i>
                      </button>
                      <button wire:click="toggleProductStatus({{ $product->id }})" type="button" class="btn btn-sm btn-icon btn-label-secondary" title="Toggle">
                        <i class="ti ti-power"></i>
                      </button>
                      <button wire:click="confirmDeleteProduct({{ $product->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Delete') }}">
                        <i class="ti ti-trash"></i>
                      </button>
                    @endif
                    @if(! $this->canAddInventoryStock() && ! $this->canManageInventory())
                      <span class="text-muted">---</span>
                    @endif
                  </div>
                  @if($confirmedProductId === $product->id)
                    <button wire:click="deleteProduct({{ $product->id }})" type="button" class="btn btn-xs btn-danger mt-1">
                      {{ __('Sure?') }}
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="text-center text-muted py-5">{{ __('ui.no_products_yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-body border-top">
        {{ $products->links() }}
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('ui.latest_movements') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ __('ui.product') }}</th>
              <th class="text-center">{{ __('ui.type') }}</th>
              <th class="text-center">{{ __('ui.quantity') }}</th>
              <th class="text-center">{{ __('ui.balance_after') }}</th>
              <th>{{ __('ui.note') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($movements as $movement)
              <tr>
                <td class="amount-cell">{{ $movement->occurred_at?->timezone('Asia/Dubai')->format('d-m-Y h:i A') }}</td>
                <td>{{ $movement->product?->name }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $movement->type === 'sale' ? 'success' : ($movement->type === 'purchase' ? 'primary' : 'warning') }}">
                    {{ ucfirst($movement->type) }}
                  </span>
                </td>
                <td class="amount-cell">{{ $this->formatQuantity($movement->quantity, $movement->product?->unit) }}</td>
                <td class="amount-cell">{{ $this->formatQuantity($movement->balance_after, $movement->product?->unit) }}</td>
                <td>{{ $movement->note ?: '---' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">{{ __('ui.no_movements_yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="inventoryProductModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editingProductId ? __('ui.edit_product') : __('ui.new_product') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.name') }}</label>
                <input wire:model.defer="productForm.name" type="text" class="form-control @error('productForm.name') is-invalid @enderror">
                @error('productForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.category') }}</label>
                @if($account === 'perfumes')
                  <input type="text" class="form-control" value="{{ __('ui.inventory_category_perfumes') }}" disabled>
                @else
                  <select wire:model.live="productForm.category" class="form-select @error('productForm.category') is-invalid @enderror">
                    <option value="">{{ __('ui.choose_category') }}</option>
                    @foreach($categoryOptions as $categoryKey => $categoryOption)
                      <option value="{{ $categoryKey }}">{{ __($categoryOption['label']) }}</option>
                    @endforeach
                  </select>
                @endif
                @error('productForm.category')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('ui.sku') }}</label>
                <input wire:model.defer="productForm.sku" type="text" class="form-control @error('productForm.sku') is-invalid @enderror">
                @error('productForm.sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              @if($this->categoryHasHairAttributes())
                <div class="col-md-4">
                  <label class="form-label">{{ __('ui.color') }}</label>
                  <input wire:model.defer="productForm.color" type="text" class="form-control @error('productForm.color') is-invalid @enderror" placeholder="{{ __('ui.product_color_placeholder') }}">
                  @error('productForm.color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">{{ __('ui.length') }}</label>
                  <select wire:model.defer="productForm.length_cm" class="form-select @error('productForm.length_cm') is-invalid @enderror">
                    <option value="">{{ __('ui.no_length') }}</option>
                    @foreach($hairLengths as $length)
                      <option value="{{ $length }}">{{ $length }} cm</option>
                    @endforeach
                  </select>
                  @error('productForm.length_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif
              <div class="col-12">
                <div class="product-image-upload-panel">
                  <div class="product-image-upload-heading">
                    <span class="product-image-upload-icon" aria-hidden="true"><i class="ti ti-cloud-upload"></i></span>
                    <div class="product-image-upload-copy">
                      <label for="product-image-file" class="form-label fw-semibold mb-1">رفع صورة المنتج</label>
                      <div class="small text-muted">JPG أو PNG أو WEBP، بحد أقصى 5 ميغابايت.</div>
                    </div>
                    <label for="product-image-file" class="btn btn-sm btn-label-success mb-0">
                      <i class="ti ti-photo-plus me-1"></i>اختيار صورة
                    </label>
                    <input id="product-image-file" wire:model="productImage" type="file" accept="image/jpeg,image/png,image/webp" class="visually-hidden">
                  </div>
                  @error('productImage')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                  <div wire:loading wire:target="productImage" class="small text-muted mt-2">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    جاري رفع الصورة...
                  </div>
                  @if($productImage && str_starts_with($productImage->getMimeType(), 'image/'))
                    <div class="product-image-upload-preview">
                      <img src="{{ $productImage->temporaryUrl() }}" alt="معاينة الصورة المختارة">
                      <div class="product-image-upload-preview-copy">
                        <div class="fw-semibold">تم اختيار الصورة</div>
                        <small class="text-muted">{{ $productImage->getClientOriginalName() }}</small>
                      </div>
                    </div>
                  @elseif($this->editingProductImageUrl())
                    <div class="product-image-upload-preview">
                      <img src="{{ $this->editingProductImageUrl() }}" alt="صورة المنتج الحالية">
                      <div class="product-image-upload-preview-copy fw-semibold">الصورة الحالية</div>
                    </div>
                  @endif
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('ui.unit') }}</label>
                <select wire:model.defer="productForm.unit" class="form-select @error('productForm.unit') is-invalid @enderror" disabled>
                  <option value="piece">{{ __('ui.piece') }}</option>
                  <option value="gram">{{ __('ui.gram') }}</option>
                </select>
                @error('productForm.unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ $editingProductId ? __('ui.current_stock') : __('ui.opening_stock') }}</label>
                <input wire:model.defer="productForm.stock_quantity" type="number" min="0" step="{{ $productForm['unit'] === 'gram' ? '0.001' : '1' }}" inputmode="{{ $productForm['unit'] === 'gram' ? 'decimal' : 'numeric' }}" class="form-control amount-cell @error('productForm.stock_quantity') is-invalid @enderror" @disabled($editingProductId && ! $this->canManageInventory())>
                @error('productForm.stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('ui.sale_price') }}</label>
                <input wire:model.defer="productForm.unit_price" type="number" min="0" step="0.01" class="form-control amount-cell @error('productForm.unit_price') is-invalid @enderror">
                @error('productForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('ui.low_stock_alert') }}</label>
                <input wire:model.defer="productForm.low_stock_threshold" type="number" min="0" step="{{ $productForm['unit'] === 'gram' ? '0.001' : '1' }}" inputmode="{{ $productForm['unit'] === 'gram' ? 'decimal' : 'numeric' }}" class="form-control amount-cell @error('productForm.low_stock_threshold') is-invalid @enderror">
                @error('productForm.low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('ui.status') }}</label>
                <select wire:model.defer="productForm.is_active" class="form-select @error('productForm.is_active') is-invalid @enderror">
                  <option value="1">{{ __('ui.active') }}</option>
                  <option value="0">{{ __('ui.inactive') }}</option>
                </select>
                @error('productForm.is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('ui.note') }}</label>
                <textarea wire:model.defer="productForm.note" class="form-control @error('productForm.note') is-invalid @enderror"></textarea>
                @error('productForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
            <button wire:click="saveProduct" type="button" class="btn btn-primary">{{ __('ui.save') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="inventoryStockModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('ui.stock_movement') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.type') }}</label>
                <select wire:model.defer="stockForm.type" class="form-select @error('stockForm.type') is-invalid @enderror">
                  <option value="purchase">{{ __('ui.add_stock') }}</option>
                  @if($this->canManageInventory())
                    <option value="decrease">{{ __('ui.decrease_stock') }}</option>
                    <option value="adjustment">{{ __('ui.set_current_stock') }}</option>
                  @endif
                </select>
                @error('stockForm.type')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.quantity') }}</label>
                <input wire:model.defer="stockForm.quantity" type="number" min="0" step="0.001" class="form-control amount-cell @error('stockForm.quantity') is-invalid @enderror">
                @error('stockForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.unit_price') }}</label>
                <input wire:model.defer="stockForm.unit_price" type="number" min="0" step="0.01" class="form-control amount-cell @error('stockForm.unit_price') is-invalid @enderror">
                @error('stockForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('ui.note') }}</label>
                <textarea wire:model.defer="stockForm.note" class="form-control @error('stockForm.note') is-invalid @enderror"></textarea>
                @error('stockForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
            <button wire:click="saveStockMovement" type="button" class="btn btn-primary">{{ __('ui.save') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="inventoryImagePreviewModal" tabindex="-1" aria-labelledby="inventoryImagePreviewTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered inventory-image-preview-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="inventoryImagePreviewTitle">معاينة المنتج</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="inventory-image-stage">
              <img id="inventoryImagePreview" src="" alt="">
            </div>
            <div class="inventory-image-preview-actions">
              <a id="inventoryImageOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-label-info">
                <i class="ti ti-external-link me-1"></i>عرض الصورة
              </a>
              <a id="inventoryImageDownloadLink" href="#" download class="btn btn-label-success">
                <i class="ti ti-download me-1"></i>تحميل الصورة
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @section('page-script')
    <script>
      (() => {
        const modal = document.getElementById('inventoryImagePreviewModal');

        if (!modal || modal.dataset.initialized) {
          return;
        }

        modal.dataset.initialized = 'true';

        modal.addEventListener('show.bs.modal', event => {
          const trigger = event.relatedTarget;
          const imageUrl = trigger?.dataset.imageUrl;

          if (!imageUrl) {
            return;
          }

          const productName = trigger.dataset.productName || 'معاينة المنتج';
          const image = document.getElementById('inventoryImagePreview');

          image.src = imageUrl;
          image.alt = productName;
          document.getElementById('inventoryImagePreviewTitle').textContent = productName;
          document.getElementById('inventoryImageOpenLink').href = imageUrl;
          document.getElementById('inventoryImageDownloadLink').href = imageUrl;
        });

        modal.addEventListener('hidden.bs.modal', () => {
          document.getElementById('inventoryImagePreview').removeAttribute('src');
        });
      })();
    </script>
  @endsection
</div>
