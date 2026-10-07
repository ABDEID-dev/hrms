<div>
  @section('title', 'تتبع الأدمن')

  @section('page-style')
    <style>
      .admin-tracking-page input[type="date"],
      .admin-tracking-page .date-text {
          direction: ltr;
          unicode-bidi: plaintext;
          white-space: nowrap;
      }

      .admin-tracking-page [x-cloak] {
          display: none !important;
      }

      .admin-tracking-page .summary-card,
      .admin-tracking-page .tracking-card,
      .admin-tracking-page .activity-card {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.34);
          border-radius: 0.75rem;
          background: rgba(var(--bs-body-bg-rgb), 0.45);
      }

      .admin-tracking-page .summary-card {
          padding: 1rem;
      }

      .admin-tracking-page .summary-value {
          font-size: 1.35rem;
          font-weight: 800;
      }

      .admin-tracking-page .event-grid {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0.85rem;
      }

      .admin-tracking-page .tracking-card,
      .admin-tracking-page .activity-card {
          padding: 1rem;
      }

      .admin-tracking-page .event-head {
          display: flex;
          align-items: flex-start;
          justify-content: space-between;
          gap: 1rem;
      }

      .admin-tracking-page .event-icon {
          width: 38px;
          height: 38px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 0.7rem;
          background: rgba(115, 103, 240, 0.16);
      }

      .admin-tracking-page .coord {
          direction: ltr;
          unicode-bidi: plaintext;
          font-size: 0.92rem;
          font-weight: 800;
          word-break: break-word;
      }

      .admin-tracking-page .field-grid {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0.7rem;
      }

      .admin-tracking-page .field-box {
          padding: 0.75rem;
          border-radius: 0.65rem;
          background: rgba(255, 255, 255, 0.035);
      }

      .admin-tracking-page .tracking-empty {
          border: 1px dashed rgba(var(--bs-border-color-rgb), 0.45);
          border-radius: 0.75rem;
          padding: 1.15rem;
          text-align: center;
          color: var(--bs-secondary-color);
          background: rgba(255, 255, 255, 0.025);
      }

      .admin-tracking-page .location-gate {
          min-height: 360px;
          display: flex;
          align-items: center;
          justify-content: center;
          text-align: center;
      }

      @media (max-width: 767.98px) {
          .admin-tracking-page {
              padding-top: 0.75rem;
          }

          .admin-tracking-page .event-grid,
          .admin-tracking-page .field-grid {
              grid-template-columns: 1fr;
          }

          .admin-tracking-page .filters-row > * {
              width: 100%;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="admin-tracking-page" x-data="adminTrackingLocationGate(false)" x-init="init()">
    <div x-show="!locationReady" class="card location-gate">
      <div class="card-body">
        <div class="mb-3">
          <span class="event-icon text-primary mx-auto"><i class="ti ti-map-pin"></i></span>
        </div>
        <h5 class="mb-2">تأكيد موقع الجهاز</h5>
        <p class="text-muted mb-3">
          يجب السماح بتحديد الموقع بدقة قبل عرض سجل التتبع.
        </p>
        <button x-on:click="requestLocation()" x-bind:disabled="loading" type="button" class="btn btn-primary">
          <span x-show="!loading">السماح بالموقع</span>
          <span x-show="loading">جاري تحديد الموقع...</span>
        </button>
        <div x-show="errorMessage" x-text="errorMessage" class="text-danger mt-3 small"></div>
      </div>
    </div>

    <div x-show="locationReady" x-cloak>
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="mb-1">تتبع الأدمن</h5>
          <small class="text-muted">عرض تفصيلي لفتح السيستم وسجل العمليات حسب الفترة المحددة.</small>
        </div>
        <div class="badge bg-label-primary">{{ $stats['actions'] + $stats['opens'] }} سجل</div>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end filters-row">
          <div class="col-lg-4 col-md-6">
            <label class="form-label">من تاريخ</label>
            <input wire:model.live="fromDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
          <div class="col-lg-4 col-md-6">
            <label class="form-label">إلى تاريخ</label>
            <input wire:model.live="toDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
          <div class="col-lg-4 col-md-12 d-flex gap-2">
            <button wire:click="resetToToday" type="button" class="btn btn-outline-primary w-100">اليوم</button>
            <button wire:click="resetToThisMonth" type="button" class="btn btn-primary w-100">هذا الشهر</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">فتح السيستم</div>
          <div class="summary-value text-info">{{ $stats['opens'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">كل العمليات</div>
          <div class="summary-value text-primary">{{ $stats['actions'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">عمليات التعديل</div>
          <div class="summary-value text-warning">{{ $stats['updates'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">عمليات الحذف</div>
          <div class="summary-value text-danger">{{ $stats['deletes'] }}</div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-1">أماكن فتح السيستم</h5>
          <small class="text-muted">كل مرة تم فتح السيستم مع الوقت والإحداثيات والدقة التقريبية.</small>
        </div>
        <span class="badge bg-label-info">{{ $stats['opens'] }} عملية</span>
      </div>
      <div class="card-body">
        <div class="event-grid">
          @forelse(($openEvents ?? collect()) as $event)
            <div class="tracking-card">
              <div class="event-head">
                <div class="d-flex align-items-center gap-2">
                  <span class="event-icon text-info"><i class="ti ti-device-desktop"></i></span>
                  <div>
                    <div class="fw-bold">فتح السيستم</div>
                    <small class="text-muted">وقت ومكان الفتح</small>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold date-text">{{ optional($event->occurred_at)->format('Y-m-d') }}</div>
                  <small class="text-muted">{{ optional($event->occurred_at)->format('H:i:s') }}</small>
                </div>
              </div>
              <div class="mt-3">
                <div class="small text-muted mb-1">الإحداثيات</div>
                <div class="coord text-info">{{ $event->latitude }}, {{ $event->longitude }}</div>
                @if($event->accuracy)
                  <div class="small text-muted mt-1">الدقة التقريبية: ± {{ number_format((float) $event->accuracy, 0) }} متر</div>
                @endif
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="btn btn-sm btn-label-primary">
                  <i class="ti ti-map-pin me-1"></i>فتح الخريطة
                </a>
                @if($event->ip_address)
                  <span class="badge bg-label-secondary">{{ $event->ip_address }}</span>
                @endif
              </div>
            </div>
          @empty
            <div class="tracking-empty">لا توجد عمليات فتح سيستم في هذه الفترة.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-1">سجل العمليات</h5>
          <small class="text-muted">عمليات الإضافة والتعديل والحذف والدخول والخروج المسجلة في السيستم.</small>
        </div>
        <span class="badge bg-label-primary">{{ $stats['actions'] }} عملية</span>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-3">
          @forelse($activityRows as $row)
            <div class="activity-card">
              <div class="event-head">
                <div class="d-flex align-items-center gap-2">
                  <span class="event-icon text-primary"><i class="ti ti-activity"></i></span>
                  <div>
                    <div class="fw-bold">
                      <span class="badge bg-label-primary me-1">{{ $row['action_label'] }}</span>
                      {{ $row['model'] }}
                      @if($row['model_id'])
                        <span class="text-muted">#{{ $row['model_id'] }}</span>
                      @endif
                    </div>
                    <small class="text-muted">{{ $row['message'] }}</small>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold date-text">{{ \Carbon\Carbon::parse($row['logged_at'])->format('Y-m-d') }}</div>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($row['logged_at'])->format('H:i:s') }}</small>
                </div>
              </div>

              <div class="field-grid mt-3">
                <div class="field-box">
                  <div class="small text-muted mb-2">قبل</div>
                  @forelse($row['old'] as $field => $value)
                    <div class="d-flex justify-content-between gap-2 small mb-1">
                      <span class="text-muted">{{ $field }}</span>
                      <span class="fw-bold text-warning text-break">{{ $value }}</span>
                    </div>
                  @empty
                    <span class="text-muted small">لا يوجد</span>
                  @endforelse
                </div>
                <div class="field-box">
                  <div class="small text-muted mb-2">بعد</div>
                  @forelse($row['new'] as $field => $value)
                    <div class="d-flex justify-content-between gap-2 small mb-1">
                      <span class="text-muted">{{ $field }}</span>
                      <span class="fw-bold text-success text-break">{{ $value }}</span>
                    </div>
                  @empty
                    <span class="text-muted small">لا يوجد</span>
                  @endforelse
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 mt-3">
                @if($row['method'])
                  <span class="badge bg-label-secondary">{{ $row['method'] }}</span>
                @endif
                @if($row['ip'])
                  <span class="badge bg-label-secondary">{{ $row['ip'] }}</span>
                @endif
                @if($row['url'])
                  <span class="badge bg-label-info text-break">{{ $row['url'] }}</span>
                @endif
              </div>

              @if($row['location'])
                <div class="field-box mt-3">
                  <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                    <div>
                      <div class="small text-muted mb-1">أقرب موقع فتح قبل العملية</div>
                      <div class="coord text-info">{{ $row['location']['latitude'] }}, {{ $row['location']['longitude'] }}</div>
                      <div class="small text-muted mt-1">
                        وقت فتح السيستم:
                        <span class="date-text">{{ $row['location']['occurred_at'] }}</span>
                        @if($row['location']['accuracy'])
                          - الدقة: ± {{ number_format((float) $row['location']['accuracy'], 0) }} متر
                        @endif
                      </div>
                    </div>
                    <a
                      href="https://www.google.com/maps?q={{ $row['location']['latitude'] }},{{ $row['location']['longitude'] }}"
                      target="_blank"
                      class="btn btn-sm btn-label-primary"
                    >
                      <i class="ti ti-map-pin me-1"></i>فتح الخريطة
                    </a>
                  </div>
                </div>
              @endif
            </div>
          @empty
            <div class="tracking-empty">لا توجد عمليات مسجلة في هذه الفترة.</div>
          @endforelse
        </div>
      </div>
    </div>
    </div>
  </div>

  @section('page-script')
    <script>
      window.adminTrackingLocationGate = function (requiresLocation = true) {
        return {
          loading: false,
          locationReady: !requiresLocation,
          errorMessage: '',
          requiredAccuracyMeters: 200,

          init() {
            if (requiresLocation) {
              this.requestLocation();
            }
          },

          requestLocation() {
            if (!navigator.geolocation) {
              this.errorMessage = 'المتصفح لا يدعم تحديد الموقع.';
              return;
            }

            this.loading = true;
            this.errorMessage = '';

            this.getPosition({ enableHighAccuracy: true, timeout: 35000, maximumAge: 0 })
              .catch(() => this.getPosition({ enableHighAccuracy: false, timeout: 25000, maximumAge: 60000 }))
              .then(position => this.ensurePreciseLocation(position))
              .then(position => this.saveLocation(position))
              .then(() => {
                this.locationReady = true;
                if (this.$wire?.refreshReport) {
                  this.$wire.refreshReport();
                }
              })
              .catch(error => {
                this.errorMessage = error?.message === 'low_accuracy'
                  ? 'يجب تفعيل الموقع الدقيق قبل عرض الصفحة. افتح إعدادات المتصفح للموقع واجعل Location = Allow وشغل Precise Location ثم اضغط السماح بالموقع.'
                  : 'لا يمكن عرض الصفحة قبل السماح بالموقع. تأكد من تفعيل Location Services ثم اضغط السماح بالموقع.';
              })
              .finally(() => {
                this.loading = false;
              });
          },

          getPosition(options) {
            return new Promise((resolve, reject) => {
              navigator.geolocation.getCurrentPosition(resolve, reject, options);
            });
          },

          ensurePreciseLocation(position) {
            if (position.coords.accuracy && position.coords.accuracy > this.requiredAccuracyMeters) {
              throw new Error('low_accuracy');
            }

            return position;
          },

          saveLocation(position) {
            const payload = {
              latitude: position.coords.latitude,
              longitude: position.coords.longitude,
              accuracy: position.coords.accuracy,
              capturedAt: Date.now()
            };

            window.__lastAttendanceLocation = payload;
            sessionStorage.setItem('attendance-location-cache', JSON.stringify(payload));

            return fetch('{{ route('employee-location-system-open') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                latitude: payload.latitude,
                longitude: payload.longitude,
                accuracy: payload.accuracy
              })
            }).then(response => {
              if (!response.ok) {
                throw new Error('location_save_failed');
              }
            });
          }
        };
      };
    </script>
  @endsection
</div>
