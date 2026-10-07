<div>
  @section('title', __('ui.management_responses_center'))

  @section('page-style')
    <style>
      .management-responses .response-hero {
          border: 1px solid rgba(115, 103, 240, 0.24);
          background: linear-gradient(135deg, rgba(115, 103, 240, 0.18), rgba(40, 199, 111, 0.10));
          border-radius: 0.75rem;
      }

      .management-responses .response-stat {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.4);
          border-radius: 1rem;
          padding: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.58);
          height: 100%;
      }

      .management-responses .response-list {
          display: flex;
          flex-direction: column;
          gap: 0.9rem;
          max-height: 520px;
          overflow: auto;
      }

      .management-responses .response-item,
      .management-responses .message-item {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 1rem;
          padding: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.48);
      }

      .management-responses .response-item.is-selected {
          border-color: rgba(115, 103, 240, 0.7);
          box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.12);
      }

      .management-responses .response-item.is-latest {
          border-color: rgba(40, 199, 111, 0.55);
          background: rgba(40, 199, 111, 0.08);
      }

        .management-responses .request-decision {
          display: flex;
          align-items: flex-start;
          gap: 0.75rem;
          padding: 0.85rem 1rem;
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.4);
          border-radius: 0.65rem;
      }

        .management-responses .request-decision.approved {
          border-color: rgba(var(--bs-success-rgb), 0.4);
          background: rgba(var(--bs-success-rgb), 0.08);
      }

        .management-responses .request-decision.rejected {
          border-color: rgba(var(--bs-danger-rgb), 0.4);
          background: rgba(var(--bs-danger-rgb), 0.07);
      }

        .management-responses .request-decision.neutral {
          border-color: rgba(var(--bs-info-rgb), 0.35);
          background: rgba(var(--bs-info-rgb), 0.06);
      }

        .management-responses .request-decision-icon {
          display: grid;
          width: 38px;
          height: 38px;
          flex: 0 0 38px;
          place-items: center;
          border-radius: 0.45rem;
          font-size: 1.2rem;
        }

        .management-responses .request-decision.approved .request-decision-icon {
          color: var(--bs-success);
          background: rgba(var(--bs-success-rgb), 0.13);
        }

        .management-responses .request-decision.rejected .request-decision-icon {
          color: var(--bs-danger);
          background: rgba(var(--bs-danger-rgb), 0.12);
        }

        .management-responses .request-decision.neutral .request-decision-icon {
          color: var(--bs-info);
          background: rgba(var(--bs-info-rgb), 0.12);
        }

        .management-responses .request-decision-content {
          min-width: 0;
          flex: 1 1 auto;
        }

        .management-responses .request-decision-title {
          margin-bottom: 0.2rem;
          font-weight: 700;
        }

        .management-responses .request-decision-message {
          line-height: 1.65;
          overflow-wrap: anywhere;
      }

      .management-responses .message-list {
          display: flex;
          flex-direction: column;
          gap: 0.85rem;
          max-height: 430px;
          overflow: auto;
      }

      .management-responses .response-body {
          white-space: pre-wrap;
      }

      .management-responses .message-status {
          font-size: 0.82rem;
      }

      .management-responses .document-strip {
          display: flex;
          flex-direction: column;
          gap: 0.75rem;
      }

      .management-responses .response-icon {
          width: 52px;
          height: 52px;
          border-radius: 1rem;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          background: rgba(115, 103, 240, 0.16);
          color: #7367f0;
          font-size: 1.5rem;
      }

      @media (max-width: 575.98px) {
          .management-responses {
              margin-inline: -0.75rem;
          }

          .management-responses .response-hero {
              border-radius: 0;
          }

          .management-responses .card-body {
              padding: 1rem;
          }

          .management-responses .response-hero h3 {
              font-size: 1.25rem;
          }

          .management-responses .response-stat {
              padding: 0.85rem;
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 1rem;
          }

            .management-responses .response-item,
            .management-responses .request-decision {
              padding: 0.85rem;
          }

          .management-responses .message-item {
              padding: 0.85rem;
          }

          .management-responses .response-list,
          .management-responses .message-list {
              max-height: none;
          }

          .management-responses .card-header {
              padding: 1rem;
          }

          .management-responses .response-icon {
              width: 44px;
              height: 44px;
              font-size: 1.25rem;
          }

          .management-responses .hero-badges {
              width: 100%;
          }

          .management-responses .hero-badges .badge {
              flex: 1 1 100%;
              justify-content: center;
              display: inline-flex;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="management-responses">
    <div class="card response-hero mb-4">
      <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
          <div class="response-icon">
            <i class="ti ti-message-check"></i>
          </div>
          <div>
            <span class="badge bg-label-primary mb-2">{{ $isAdminView ? __('ui.admin') : __('ui.employee_portal') }}</span>
            <h3 class="mb-1">{{ __('ui.management_responses_center') }}</h3>
            <p class="text-muted mb-0">{{ $isAdminView ? __('ui.admin_management_responses_hint') : __('ui.management_responses_hint') }}</p>
          </div>
        </div>
        <div class="hero-badges d-flex flex-wrap gap-2">
          @unless($showOnlyNotifications)
            <span class="badge bg-label-success">
              <i class="ti ti-circle-check me-1"></i>{{ __('ui.approved') }}: {{ $responseStats['approved'] }}
            </span>
            <span class="badge bg-label-info">
              <i class="ti ti-message-reply me-1"></i>{{ __('ui.replied') }}: {{ $responseStats['replied'] }}
            </span>
            <span class="badge bg-label-primary">
              <i class="ti ti-clock me-1"></i>{{ __('ui.pending') }}: {{ $responseStats['pending'] }}
            </span>
          @endunless
        </div>
      </div>
    </div>

    @if($showOnlyNotifications)
      <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row justify-content-between gap-2">
          <div>
            <h5 class="mb-0">{{ __('ui.notifications') }}</h5>
            <small class="text-muted">{{ __('ui.latest_notifications_hint') }}</small>
          </div>
          @if ($unreadNotificationsCount)
            <button wire:click="markAllNotificationsAsRead" type="button" class="btn btn-sm btn-outline-primary">
              {{ __('ui.mark_all_as_read') }}
            </button>
          @endif
        </div>
        <div class="card-body">
          @if ($recentNotifications->count())
            <div class="message-list">
              @foreach ($recentNotifications as $notification)
                <div class="message-item">
                  <div class="d-flex justify-content-between gap-2">
                    <div class="fw-semibold">{{ $notification->data['user'] ?? __('ui.system') }}</div>
                    @if (is_null($notification->read_at))
                      <button
                        wire:click="markNotificationAsRead('{{ $notification->id }}')"
                        type="button"
                        class="btn btn-xs btn-outline-primary"
                      >
                        {{ __('ui.read') }}
                      </button>
                    @endif
                  </div>
                  <div class="mt-2 response-body">{{ $notification->data['message'] ?? '---' }}</div>
                  <div class="small text-muted mt-2">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center text-muted py-4">
              <i class="ti ti-bell-off d-block fs-1 mb-2"></i>
              {{ __('ui.no_notifications_now') }}
            </div>
          @endif
        </div>
      </div>
    @else
    <div class="row g-4">
      <div class="col-xl-4 col-md-6">
        <div class="response-stat">
          <div class="text-muted small mb-2">{{ __('ui.approved') }}</div>
          <div class="h2 mb-1 text-success">{{ $responseStats['approved'] }}</div>
          <div class="small text-muted">{{ __('ui.approved_requests_hint') }}</div>
        </div>
      </div>

      <div class="col-xl-4 col-md-6">
        <div class="response-stat">
          <div class="text-muted small mb-2">{{ __('ui.replied') }}</div>
          <div class="h2 mb-1 text-info">{{ $responseStats['replied'] }}</div>
          <div class="small text-muted">{{ __('ui.replied_requests_hint') }}</div>
        </div>
      </div>

      <div class="col-xl-4 col-md-12">
        <div class="response-stat">
          <div class="text-muted small mb-2">{{ __('ui.pending') }}</div>
          <div class="h2 mb-1 text-primary">{{ $responseStats['pending'] }}</div>
          <div class="small text-muted">{{ __('ui.pending_requests_hint') }}</div>
        </div>
      </div>

      <div class="col-xl-4 order-2 order-xl-1">
        <div class="row g-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">{{ __('ui.management_alerts') }}</h5>
                <small class="text-muted">{{ __('ui.management_alerts_hint') }}</small>
              </div>
              <div class="card-body">
                @if ($managementAlert)
                  <div class="alert alert-primary mb-0">
                    <div class="fw-semibold mb-2">{{ __('ui.management_message') }}</div>
                    <div class="response-body">{{ $managementAlert['body'] }}</div>
                    @if (!empty($managementAlert['updated_at']))
                      <div class="small text-muted mt-3">{{ $managementAlert['updated_at']->diffForHumans() }}</div>
                    @endif
                  </div>
                @else
                  <div class="text-center text-muted py-4">
                    <i class="ti ti-message-off d-block fs-1 mb-2"></i>
                    {{ __('ui.no_management_alerts') }}
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex flex-column flex-sm-row justify-content-between gap-2">
                <div>
                  <h5 class="mb-0">{{ __('ui.notifications') }}</h5>
                  <small class="text-muted">{{ __('ui.latest_notifications_hint') }}</small>
                </div>
                <button wire:click="toggleNotifications" type="button" class="btn btn-sm btn-outline-secondary">
                  {{ $showNotifications ? __('ui.hide_notifications') : __('ui.show_notifications') }}
                </button>
                @if ($showNotifications && $unreadNotificationsCount)
                  <button wire:click="markAllNotificationsAsRead" type="button" class="btn btn-sm btn-outline-primary">
                    {{ __('ui.mark_all_as_read') }}
                  </button>
                @endif
              </div>
              <div class="card-body">
                @if (! $showNotifications)
                  <div class="text-center text-muted py-4">
                    <i class="ti ti-bell-off d-block fs-1 mb-2"></i>
                    {{ __('ui.notifications_hidden') }}
                  </div>
                @elseif ($recentNotifications->count())
                  <div class="message-list">
                    @foreach ($recentNotifications as $notification)
                      <div class="message-item">
                        <div class="d-flex justify-content-between gap-2">
                          <div class="fw-semibold">{{ $notification->data['user'] ?? __('ui.system') }}</div>
                          @if (is_null($notification->read_at))
                            <button
                              wire:click="markNotificationAsRead('{{ $notification->id }}')"
                              type="button"
                              class="btn btn-xs btn-outline-primary"
                            >
                              {{ __('ui.read') }}
                            </button>
                          @endif
                        </div>
                        <div class="mt-2 response-body">{{ $notification->data['message'] ?? '---' }}</div>
                        <div class="small text-muted mt-2">{{ $notification->created_at->diffForHumans() }}</div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="text-center text-muted py-4">
                    <i class="ti ti-bell-off d-block fs-1 mb-2"></i>
                    {{ __('ui.no_notifications_now') }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-8 order-1 order-xl-2">
        <div class="row g-4">
          <div class="col-12 order-2">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">{{ $isAdminView ? __('ui.all_internal_messages') : __('ui.internal_messages') }}</h5>
                <small class="text-muted">{{ $isAdminView ? __('ui.latest_internal_messages_all') : __('ui.latest_internal_messages_profile') }}</small>
              </div>
              <div class="card-body">
                @if ($recentMessages->count())
                  <div class="message-list">
                    @foreach ($recentMessages as $message)
                      <div class="message-item">
                        @if($isAdminView)
                          <div class="d-flex justify-content-between gap-2 mb-2">
                            <span class="badge bg-label-primary">{{ $message->employee?->full_name ?? '---' }}</span>
                            <span class="text-muted small">#{{ $message->employee_id }}</span>
                          </div>
                        @endif
                        <div class="fw-semibold response-body">{{ $message->text }}</div>
                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-2">
                          <span class="text-muted small">{{ $message->created_at->translatedFormat('Y-m-d H:i') }}</span>
                          <span class="message-status {{ $message->is_sent ? 'text-success' : 'text-warning' }}">
                            {{ $message->is_sent ? __('ui.delivered') : __('ui.saved_in_system') }}
                          </span>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="text-center text-muted py-4">
                    <i class="ti ti-message-off d-block fs-1 mb-2"></i>
                    {{ __('ui.no_internal_messages_yet') }}
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="col-12 order-1">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">{{ $isAdminView ? __('ui.all_employee_requests') : __('ui.my_management_replies') }}</h5>
                <small class="text-muted">{{ $isAdminView ? __('ui.all_employee_requests_hint') : __('ui.my_management_replies_hint') }}</small>
              </div>
              <div class="card-body">
                @if ($requests->count())
                  <div class="response-list">
                    @foreach ($requests as $request)
                      <div id="request-{{ $request->id }}" class="response-item {{ $selectedRequestId === $request->id ? 'is-selected' : '' }} {{ $loop->first ? 'is-latest' : '' }}">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                      <div>
                        @if($isAdminView)
                          <div class="mb-2">
                            <span class="badge bg-label-primary">{{ $request->employee?->full_name ?? '---' }}</span>
                            <span class="text-muted small mx-1">#{{ $request->employee_id }}</span>
                          </div>
                        @endif
                        <div class="fw-semibold">{{ $request->title }}</div>
                        <div class="small text-muted">{{ __('ui.request_type_'.$request->type) }}</div>
                        @if($loop->first)
                          <span class="badge bg-label-success mt-2">{{ __('ui.latest_management_response') }}</span>
                        @endif
                      </div>
                      @unless(in_array($request->status, ['approved', 'rejected', 'cancelled'], true))
                        <span class="badge {{ $request->status === 'replied' ? 'bg-label-info' : 'bg-label-primary' }}">
                          {{ __('ui.'.$request->status) }}
                        </span>
                      @endunless
                    </div>

                    @if (! is_null($request->amount))
                      <div class="small mt-2">{{ __('ui.amount') }}: {{ number_format($request->amount, 2) }}</div>
                    @endif

                    <div class="small text-muted mt-2 response-body">{{ $request->body ?: '---' }}</div>

                    @if ($request->relationLoaded('visibleDocuments') && $request->visibleDocuments->count())
                      <div class="document-strip mt-3">
                        @foreach ($request->visibleDocuments as $document)
                          <div>
                            <div class="small text-muted mb-1">{{ $document->original_name ?: __('ui.attachment_or_media') }}</div>
                            @if ($document->is_image)
                              <img src="{{ $document->url }}" alt="{{ $document->original_name }}" class="img-fluid rounded">
                            @elseif ($document->is_video)
                              <video src="{{ $document->url }}" controls preload="metadata" class="w-100 rounded"></video>
                            @elseif ($document->is_audio)
                              <audio src="{{ $document->url }}" controls preload="metadata" class="w-100"></audio>
                            @else
                              <a href="{{ $document->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-external-link me-1"></i>{{ __('ui.open_document') }}
                              </a>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    @endif

                    @php
                      $isDecision = in_array($request->status, ['approved', 'rejected', 'cancelled'], true);
                      $decisionClass = $request->status === 'approved' ? 'approved' : (in_array($request->status, ['rejected', 'cancelled'], true) ? 'rejected' : 'neutral');
                      $decisionIcon = $request->status === 'approved' ? 'ti-circle-check' : ($request->status === 'cancelled' ? 'ti-ban' : ($request->status === 'rejected' ? 'ti-circle-x' : 'ti-message-reply'));
                      $decisionTitle = $isDecision ? __('ui.'.$request->status) : __('ui.admin_response');
                      $decisionMessage = $request->admin_response;

                      if (! $decisionMessage && $request->status === 'approved' && $request->type === 'advance') {
                        $decisionMessage = __('ui.advance_will_be_deducted_from_salary');
                      } elseif (! $decisionMessage && $request->status === 'approved') {
                        $decisionMessage = __('ui.request_approved_without_note');
                      } elseif (! $decisionMessage && $request->status === 'rejected') {
                        $decisionMessage = __('ui.request_rejected_without_note');
                      } elseif (! $decisionMessage && $request->status === 'cancelled') {
                        $decisionMessage = __('ui.request_cancelled_notification', ['title' => $request->title]);
                      }
                    @endphp

                    @if($isDecision || $request->admin_response)
                      <div class="request-decision {{ $decisionClass }} mt-3">
                        <span class="request-decision-icon" aria-hidden="true"><i class="ti {{ $decisionIcon }}"></i></span>
                        <div class="request-decision-content">
                          <div class="request-decision-title">{{ $decisionTitle }}</div>
                          @if($decisionMessage)
                            <div class="request-decision-message response-body">{{ $decisionMessage }}</div>
                          @endif
                        </div>
                      </div>
                    @endif

                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-3 small text-muted">
                      <span>{{ $request->created_at->translatedFormat('Y-m-d H:i') }}</span>
                      @if ($request->reviewed_at)
                        <span>{{ __('ui.last_reviewed_at') }}: {{ $request->reviewed_at->translatedFormat('Y-m-d H:i') }}</span>
                      @endif
                    </div>

                    @unless($isAdminView)
                      <div class="d-flex flex-wrap gap-2 mt-3">
                        @if($request->status === 'pending')
                          <button wire:click="cancelRequest({{ $request->id }})" type="button" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-x me-1"></i>{{ __('ui.cancel_request') }}
                          </button>
                        @endif
                        <button wire:click="hideRequest({{ $request->id }})" type="button" class="btn btn-sm btn-outline-secondary">
                          <i class="ti ti-eye-off me-1"></i>{{ __('ui.remove_from_my_center') }}
                        </button>
                      </div>
                    @endunless
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center text-muted py-5">
                <i class="ti ti-inbox d-block fs-1 mb-2"></i>
                {{ __('ui.no_requests_yet') }}
              </div>
            @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
