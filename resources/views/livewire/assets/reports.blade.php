<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', app()->getLocale() === 'ar' ? 'تقارير المخزون' : 'Inventory Reports')

  @section('page-style')
    <style>
      .inventory-reports .amount-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .inventory-reports .report-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .85rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        height: 100%;
      }

      .inventory-reports .report-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.05rem;
      }

      .inventory-reports .sheet-table th {
        background: #264b12;
        color: #fff;
        vertical-align: middle;
        white-space: nowrap;
      }

      .inventory-reports .section-title {
        font-weight: 700;
      }

      @media (max-width: 767.98px) {
        .inventory-reports {
          margin-inline: -.75rem;
        }

        .inventory-reports .card {
          border-radius: 0;
        }

        .inventory-reports .table {
          min-width: 980px;
        }
      }
    </style>
  @endsection

  @php
    $isArabic = app()->getLocale() === 'ar';
    $labels = [
      'title' => $isArabic ? 'تقارير المخزون' : 'Inventory Reports',
      'hint' => $isArabic ? 'ملخص كامل للمنتجات، الرصيد، الحركات، والقيمة حسب الفترة.' : 'Full summary for products, balances, movements, and value by date range.',
      'all_accounts' => $isArabic ? 'كل الفروع' : 'All Accounts',
      'all_categories' => $isArabic ? 'كل الفئات' : 'All Categories',
      'from' => $isArabic ? 'من تاريخ' : 'From',
      'to' => $isArabic ? 'إلى تاريخ' : 'To',
      'reset' => $isArabic ? 'إعادة ضبط' : 'Reset',
      'stock_now' => $isArabic ? 'الرصيد الحالي' : 'Current Stock',
      'period_movements' => $isArabic ? 'حركات الفترة' : 'Period Movements',
      'stock_value' => $isArabic ? 'قيمة المخزون' : 'Stock Value',
      'purchase_amount' => $isArabic ? 'قيمة الإضافات' : 'Purchase Amount',
      'sales_amount' => $isArabic ? 'قيمة البيع' : 'Sales Amount',
      'purchased' => $isArabic ? 'المضاف' : 'Purchased',
      'adjusted' => $isArabic ? 'التسويات' : 'Adjustments',
      'by_account' => $isArabic ? 'ملخص حسب الفرع' : 'By Account',
      'by_category' => $isArabic ? 'ملخص حسب الفئة' : 'By Category',
      'low_stock' => $isArabic ? 'منتجات منخفضة الرصيد' : 'Low Stock Products',
      'all_products' => $isArabic ? 'كل المنتجات' : 'All Products',
      'movement_log' => $isArabic ? 'سجل الحركات حسب التاريخ' : 'Movement Log By Date',
      'account' => $isArabic ? 'الفرع' : 'Account',
      'value' => $isArabic ? 'القيمة' : 'Value',
      'no_data' => $isArabic ? 'لا توجد بيانات.' : 'No data.',
    ];
  @endphp

  <div class="inventory-reports">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-0">{{ $labels['title'] }}</h5>
          <small class="text-muted">{{ $labels['hint'] }}</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-xl-2 col-md-4">
            <label class="form-label">{{ $labels['account'] }}</label>
            <select wire:model.live="account" class="form-select">
              <option value="all">{{ $labels['all_accounts'] }}</option>
              @foreach($accountNames as $key => $name)
                <option value="{{ $key }}">{{ __('ui.inventory_account_'.$key) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xl-2 col-md-4">
            <label class="form-label">{{ __('ui.category') }}</label>
            <select wire:model.live="category" class="form-select">
              <option value="all">{{ $labels['all_categories'] }}</option>
              @foreach($categoryOptions as $key => $option)
                <option value="{{ $key }}">{{ __($option['label']) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xl-3 col-md-4">
            <label class="form-label">{{ __('ui.search_inventory') }}</label>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('ui.search_inventory') }}">
          </div>
          <div class="col-xl-2 col-md-4">
            <label class="form-label">{{ $labels['from'] }}</label>
            <input wire:model.live="dateFrom" type="date" class="form-control amount-cell">
          </div>
          <div class="col-xl-2 col-md-4">
            <label class="form-label">{{ $labels['to'] }}</label>
            <input wire:model.live="dateTo" type="date" class="form-control amount-cell">
          </div>
          <div class="col-xl-1 col-md-4">
            <button wire:click="resetFilters" type="button" class="btn btn-label-secondary w-100">
              <i class="ti ti-refresh"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.products_count') }}</div>
          <div class="report-value">{{ number_format((float) ($stockSummary->products_count ?? 0), 0) }}</div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.pieces_in_stock') }}</div>
          <div class="report-value">{{ number_format((float) ($stockSummary->pieces_stock ?? 0), 0) }}</div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.pieces_sold') }}</div>
          <div class="report-value text-success">{{ number_format((float) ($stockSummary->pieces_sold ?? 0), 0) }}</div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.grams_in_stock') }}</div>
          <div class="report-value">{{ $this->formatQuantity($stockSummary->grams_stock ?? 0, 'gram') }} g</div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.grams_sold') }}</div>
          <div class="report-value text-success">{{ $this->formatQuantity($stockSummary->grams_sold ?? 0, 'gram') }} g</div>
        </div>
      </div>
      <div class="col-xl-2 col-md-4">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ $labels['stock_value'] }}</div>
          <div class="report-value">AED {{ number_format((float) ($stockSummary->stock_value ?? 0), 2) }}</div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ $labels['purchased'] }}</div>
          <div class="report-value">{{ $this->formatQuantity($movementSummary->purchased_quantity ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ __('ui.sold') }}</div>
          <div class="report-value text-success">{{ $this->formatQuantity($movementSummary->sold_quantity ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ $labels['purchase_amount'] }}</div>
          <div class="report-value">AED {{ number_format((float) ($movementSummary->purchase_amount ?? 0), 2) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="report-box">
          <div class="text-muted small mb-1">{{ $labels['sales_amount'] }}</div>
          <div class="report-value text-success">AED {{ number_format((float) ($movementSummary->sales_amount ?? 0), 2) }}</div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-xl-6">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">{{ $labels['by_account'] }}</h5>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>{{ $labels['account'] }}</th>
                  <th class="text-center">{{ __('ui.products_count') }}</th>
                  <th class="text-center">{{ __('ui.pieces_in_stock') }}</th>
                  <th class="text-center">{{ __('ui.grams_in_stock') }}</th>
                  <th class="text-center">{{ $labels['value'] }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($accountSummaries as $row)
                  <tr>
                    <td>{{ $this->accountLabel($row->account) }}</td>
                    <td class="amount-cell">{{ number_format((float) $row->products_count, 0) }}</td>
                    <td class="amount-cell">{{ number_format((float) $row->pieces_stock, 0) }}</td>
                    <td class="amount-cell">{{ $this->formatQuantity($row->grams_stock, 'gram') }} g</td>
                    <td class="amount-cell">AED {{ number_format((float) $row->stock_value, 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted py-4">{{ $labels['no_data'] }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">{{ $labels['by_category'] }}</h5>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>{{ __('ui.category') }}</th>
                  <th class="text-center">{{ __('ui.products_count') }}</th>
                  <th class="text-center">{{ __('ui.in_stock') }}</th>
                  <th class="text-center">{{ __('ui.sold') }}</th>
                  <th class="text-center">{{ $labels['value'] }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($categorySummaries as $row)
                  <tr>
                    <td>{{ $this->categoryLabel($row->category) }}</td>
                    <td class="amount-cell">{{ number_format((float) $row->products_count, 0) }}</td>
                    <td class="amount-cell">{{ $this->formatQuantity($row->stock_quantity, $row->unit) }} {{ $row->unit === 'gram' ? __('ui.gram') : __('ui.piece') }}</td>
                    <td class="amount-cell">{{ $this->formatQuantity($row->sold_quantity, $row->unit) }} {{ $row->unit === 'gram' ? __('ui.gram') : __('ui.piece') }}</td>
                    <td class="amount-cell">AED {{ number_format((float) $row->stock_value, 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted py-4">{{ $labels['no_data'] }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ $labels['low_stock'] }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ $labels['account'] }}</th>
              <th>{{ __('ui.product') }}</th>
              <th class="text-center">{{ __('ui.category') }}</th>
              <th class="text-center">{{ __('ui.in_stock') }}</th>
              <th class="text-center">{{ __('ui.low_stock_alert') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lowStockProducts as $product)
              <tr>
                <td>{{ $this->accountLabel($product->account) }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-center">{{ $this->categoryLabel($product->category) }}</td>
                <td class="amount-cell text-danger fw-semibold">{{ $this->formatQuantity($product->stock_quantity, $product->unit) }}</td>
                <td class="amount-cell">{{ $this->formatQuantity($product->low_stock_threshold, $product->unit) }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">{{ $labels['no_data'] }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ $labels['all_products'] }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table sheet-table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ $labels['account'] }}</th>
              <th>{{ __('ui.product') }}</th>
              <th class="text-center">{{ __('ui.category') }}</th>
              <th class="text-center">{{ __('ui.sku') }}</th>
              <th class="text-center">{{ __('ui.unit') }}</th>
              <th class="text-center">{{ __('ui.in_stock') }}</th>
              <th class="text-center">{{ __('ui.sold') }}</th>
              <th class="text-center">{{ __('ui.price') }}</th>
              <th class="text-center">{{ $labels['value'] }}</th>
              <th class="text-center">{{ __('ui.status') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($products as $product)
              <tr>
                <td>{{ $this->accountLabel($product->account) }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-center">{{ $this->categoryLabel($product->category) }}</td>
                <td class="text-center">{{ $product->sku ?: '---' }}</td>
                <td class="text-center">{{ $product->unit === 'gram' ? __('ui.gram') : __('ui.piece') }}</td>
                <td class="amount-cell">{{ $this->formatQuantity($product->stock_quantity, $product->unit) }}</td>
                <td class="amount-cell">{{ $this->formatQuantity($product->sold_quantity, $product->unit) }}</td>
                <td class="amount-cell">{{ $product->unit_price !== null ? 'AED '.number_format((float) $product->unit_price, 2) : '---' }}</td>
                <td class="amount-cell">AED {{ number_format((float) $product->stock_quantity * (float) $product->unit_price, 2) }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $product->is_active ? 'success' : 'secondary' }}">
                    {{ $product->is_active ? __('ui.active') : __('ui.inactive') }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-center text-muted py-4">{{ $labels['no_data'] }}</td></tr>
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
        <h5 class="mb-0">{{ $labels['movement_log'] }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ $labels['account'] }}</th>
              <th>{{ __('ui.product') }}</th>
              <th class="text-center">{{ __('ui.type') }}</th>
              <th class="text-center">{{ __('ui.quantity') }}</th>
              <th class="text-center">{{ __('ui.unit_price') }}</th>
              <th class="text-center">{{ __('ui.amount') }}</th>
              <th class="text-center">{{ __('ui.balance_after') }}</th>
              <th>{{ __('ui.note') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($movements as $movement)
              <tr>
                <td class="amount-cell">{{ $movement->occurred_at?->timezone('Asia/Dubai')->format('d-m-Y h:i A') }}</td>
                <td>{{ $this->accountLabel($movement->account) }}</td>
                <td>{{ $movement->product?->name ?: '---' }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ $movement->type === 'sale' ? 'success' : ($movement->type === 'purchase' ? 'primary' : 'warning') }}">
                    {{ $this->movementTypeLabel($movement->type) }}
                  </span>
                </td>
                <td class="amount-cell">{{ $this->formatQuantity($movement->quantity, $movement->product?->unit) }}</td>
                <td class="amount-cell">{{ $movement->unit_price !== null ? 'AED '.number_format((float) $movement->unit_price, 2) : '---' }}</td>
                <td class="amount-cell">{{ $movement->total_amount !== null ? 'AED '.number_format((float) $movement->total_amount, 2) : '---' }}</td>
                <td class="amount-cell">{{ $this->formatQuantity($movement->balance_after, $movement->product?->unit) }}</td>
                <td>{{ $movement->note ?: '---' }}</td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center text-muted py-4">{{ __('ui.no_movements_yet') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-body border-top">
        {{ $movements->links() }}
      </div>
    </div>
  </div>
</div>
