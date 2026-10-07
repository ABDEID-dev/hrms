<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @section('title', __('ui.secret_archive'))

    @section('page-style')
        <style>
            .secret-archive-page .archive-heading {
                display: flex;
                align-items: center;
                gap: .85rem;
            }

            .secret-archive-page .archive-heading-icon {
                display: grid;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                place-items: center;
                border-radius: .4rem;
                color: var(--bs-warning);
                background: rgba(var(--bs-warning-rgb), .13);
                font-size: 1.35rem;
            }

            .secret-archive-page .folder-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: .9rem 1rem;
                border: 1px solid rgba(var(--bs-border-color-rgb), .5);
                border-radius: .45rem;
                background: rgba(var(--bs-body-bg-rgb), .35);
            }

            .secret-archive-page .folder-row.is-open {
                border-color: rgba(var(--bs-success-rgb), .5);
                background: rgba(var(--bs-success-rgb), .035);
            }

            .secret-archive-page .folder-row-main {
                display: flex;
                align-items: center;
                gap: .75rem;
                min-width: 0;
            }

            .secret-archive-page .folder-icon {
                display: grid;
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                place-items: center;
                border-radius: .35rem;
                color: var(--bs-primary);
                background: rgba(var(--bs-primary-rgb), .1);
                font-size: 1.15rem;
            }

            .secret-archive-page .archive-unlock-panel {
                max-width: 480px;
                margin-inline: auto;
                padding: 1.5rem;
                border: 1px solid rgba(var(--bs-border-color-rgb), .55);
                border-radius: .5rem;
                text-align: center;
                background: rgba(var(--bs-body-bg-rgb), .45);
            }

            .secret-archive-page .archive-unlock-icon {
                display: grid;
                width: 52px;
                height: 52px;
                margin: 0 auto .85rem;
                place-items: center;
                border-radius: .45rem;
                color: var(--bs-warning);
                background: rgba(var(--bs-warning-rgb), .13);
                font-size: 1.5rem;
            }

            .secret-archive-page .archive-file-icon {
                display: grid;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                place-items: center;
                border-radius: .35rem;
                color: var(--bs-secondary-color);
                background: rgba(var(--bs-secondary-rgb), .1);
                font-size: 1.2rem;
            }

            .secret-archive-page .archive-file-actions {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                flex-wrap: wrap;
            }

            .secret-archive-page .archive-upload-panel {
                display: flex;
                flex-direction: column;
                gap: .85rem;
            }

            .secret-archive-page .archive-upload-heading {
                display: flex;
                align-items: center;
                gap: .75rem;
            }

            .secret-archive-page .archive-upload-icon {
                display: grid;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                place-items: center;
                border-radius: .4rem;
                color: var(--bs-primary);
                background: rgba(var(--bs-primary-rgb), .1);
                font-size: 1.3rem;
            }

            .secret-archive-page .archive-selected-files {
                display: flex;
                flex-direction: column;
                gap: .45rem;
                padding: .65rem;
                border: 1px dashed rgba(var(--bs-border-color-rgb), .7);
                border-radius: .45rem;
                background: rgba(var(--bs-body-bg-rgb), .35);
            }

            .secret-archive-page .archive-selected-file {
                display: flex;
                align-items: center;
                gap: .55rem;
                padding: .45rem .6rem;
                border-radius: .35rem;
                background: rgba(var(--bs-secondary-rgb), .08);
            }

            .secret-archive-page .archive-selected-file-icon {
                color: var(--bs-secondary-color);
                font-size: 1.05rem;
            }

            .secret-archive-page .archive-selected-file-name {
                flex: 1 1 auto;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: .875rem;
            }

            .secret-archive-page .archive-selected-file-remove {
                display: grid;
                width: 28px;
                height: 28px;
                flex: 0 0 28px;
                place-items: center;
                border: 0;
                border-radius: .3rem;
                color: var(--bs-danger);
                background: rgba(var(--bs-danger-rgb), .12);
                cursor: pointer;
                line-height: 1;
            }

            .secret-archive-page .archive-selected-file-remove:hover {
                background: rgba(var(--bs-danger-rgb), .2);
            }

            .secret-archive-page .archive-pin-reveal {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                padding: .45rem .65rem;
                border: 1px dashed rgba(var(--bs-warning-rgb), .6);
                border-radius: .35rem;
                background: rgba(var(--bs-warning-rgb), .06);
                direction: ltr;
                font-variant-numeric: tabular-nums;
            }

            @media (max-width: 575.98px) {
                .secret-archive-page {
                    margin-inline: -.75rem;
                }

                .secret-archive-page .card {
                    border-radius: 0;
                }

                .secret-archive-page .folder-row {
                    align-items: flex-start;
                    flex-direction: column;
                    padding: .8rem;
                }

                .secret-archive-page .folder-row-actions,
                .secret-archive-page .folder-row-actions .btn {
                    width: 100%;
                }

                .secret-archive-page .archive-unlock-panel {
                    padding: 1rem;
                }
            }
        </style>
    @endsection

    @include('_partials/_alerts/alert-general')

    <div class="secret-archive-page">
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="archive-heading">
                    <span class="archive-heading-icon" aria-hidden="true"><i class="ti ti-lock-access"></i></span>
                    <div>
                        <h5 class="mb-1">{{ __('ui.secret_archive') }}</h5>
                        <div class="small text-muted">{{ __('ui.secret_archive_hint') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="secret-archive-account" class="form-label mb-0">{{ __('ui.secret_archive_account') }}</label>
                    <select id="secret-archive-account" wire:model.live="account" class="form-select w-auto">
                        @foreach($accounts as $archiveAccount)
                            <option value="{{ $archiveAccount }}">{{ __($accountLabels[$archiveAccount]) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($canManageCurrentAccount)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-folder-plus me-1 text-primary"></i>{{ __('ui.secret_archive_create_folder') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label">{{ __('ui.secret_archive_folder_name') }}</label>
                            <input wire:model.defer="folderName" type="text" maxlength="120" class="form-control @error('folderName') is-invalid @enderror">
                            @error('folderName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">{{ __('ui.secret_archive_pin') }}</label>
                            <input wire:model.defer="folderPin" type="password" inputmode="numeric" pattern="[0-9]*" autocomplete="new-password" class="form-control @error('folderPin') is-invalid @enderror">
                            @error('folderPin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-3 col-md-12">
                            <button wire:click="createFolder" wire:loading.attr="disabled" type="button" class="btn btn-primary w-100">
                                <i class="ti ti-folder-plus me-1"></i>{{ __('ui.secret_archive_create_folder') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('ui.secret_archive') }} - {{ __($accountLabels[$account]) }}</h6>
                <span class="badge bg-label-secondary">{{ $folders->count() }}</span>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                @forelse($folders as $folder)
                    <div class="folder-row {{ $folder->is_unlocked ? 'is-open' : '' }}" wire:key="secret-folder-{{ $folder->id }}">
                        <div class="folder-row-main">
                            <span class="folder-icon" aria-hidden="true">
                                <i class="ti {{ $folder->is_unlocked ? 'ti-folder-open' : 'ti-folder-lock' }}"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-break">{{ $folder->name }}</div>
                                <div class="small text-muted">
                                    {{ $folder->created_at?->translatedFormat('Y-m-d H:i') }}
                                    @if($folder->is_unlocked)
                                        <span class="mx-1">|</span>{{ $folder->files_count }} {{ __('ui.secret_archive_files') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="folder-row-actions d-flex align-items-center gap-2 flex-wrap">
                            @if($activeFolderId === $folder->id)
                                <button wire:click="lockFolder({{ $folder->id }})" type="button" class="btn btn-sm btn-label-secondary">
                                    <i class="ti ti-lock me-1"></i>{{ __('ui.secret_archive_lock') }}
                                </button>
                            @else
                                <button wire:click="openFolder({{ $folder->id }})" type="button" class="btn btn-sm {{ $folder->is_unlocked ? 'btn-label-success' : 'btn-label-primary' }}">
                                    <i class="ti {{ $folder->is_unlocked ? 'ti-folder-open' : 'ti-lock-open' }} me-1"></i>{{ __('ui.secret_archive_open_folder') }}
                                </button>
                            @endif
                            @if($isSuperAdmin && $revealedPinFolderId !== $folder->id)
                                <button wire:click="revealFolderPin({{ $folder->id }})" type="button" class="btn btn-sm btn-icon btn-label-warning" title="{{ __('ui.secret_archive_reveal_pin') }}" aria-label="{{ __('ui.secret_archive_reveal_pin') }}">
                                    <i class="ti ti-eye"></i>
                                </button>
                            @endif
                            @if($confirmedDeleteFolderId === $folder->id)
                                <button wire:click="deleteFolder({{ $folder->id }})" type="button" class="btn btn-sm btn-danger">{{ __('ui.confirm') }}</button>
                            @elseif($canManageCurrentAccount && $folder->is_unlocked)
                                <button wire:click="confirmDeleteFolder({{ $folder->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('ui.secret_archive_delete_folder') }}" aria-label="{{ __('ui.secret_archive_delete_folder') }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($isSuperAdmin && $revealedPinFolderId === $folder->id)
                        <div class="d-flex flex-wrap align-items-center gap-2 ps-2 pe-2 pb-2">
                            <span class="archive-pin-reveal"><i class="ti ti-key" aria-hidden="true"></i>{{ $revealedFolderPin }}</span>
                            <button wire:click="$set('revealedPinFolderId', null)" type="button" class="btn btn-sm btn-label-secondary">{{ __('ui.cancel') }}</button>
                        </div>
                    @endif
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-folder-off d-block fs-2 mb-2"></i>{{ __('ui.secret_archive_no_folders') }}
                    </div>
                @endforelse
            </div>
        </div>

        @if($activeFolder && ! $activeFolder->is_unlocked)
            <div class="card mb-4">
                <div class="card-body">
                    <div class="archive-unlock-panel">
                        <span class="archive-unlock-icon" aria-hidden="true"><i class="ti ti-lock"></i></span>
                        <h6 class="mb-1">{{ $activeFolder->name }}</h6>
                        <p class="text-muted small mb-3">{{ __('ui.secret_archive_enter_pin') }}</p>
                        <div class="input-group mb-2">
                            <input wire:model.defer="unlockPin" type="password" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="form-control text-center @error('unlockPin') is-invalid @enderror" aria-label="{{ __('ui.secret_archive_pin') }}">
                            <button wire:click="unlockFolder" type="button" class="btn btn-primary">
                                <i class="ti ti-lock-open me-1"></i>{{ __('ui.secret_archive_unlock') }}
                            </button>
                        </div>
                        @error('unlockPin')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        @endif

        @if($activeFolder && $activeFolder->is_unlocked)
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-1"><i class="ti ti-folder-open me-1 text-success"></i>{{ $activeFolder->name }}</h5>
                        <small class="text-muted">{{ __($accountLabels[$activeFolder->account]) }} · {{ $activeFiles->count() }} {{ __('ui.secret_archive_files') }}</small>
                    </div>
                    @if($isSuperAdmin)
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if($revealedPinFolderId === $activeFolder->id)
                                <span class="archive-pin-reveal"><i class="ti ti-key" aria-hidden="true"></i>{{ $revealedFolderPin }}</span>
                            @else
                                <button wire:click="revealFolderPin({{ $activeFolder->id }})" type="button" class="btn btn-sm btn-label-warning">
                                    <i class="ti ti-eye me-1"></i>{{ __('ui.secret_archive_reveal_pin') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
                @if($isSuperAdmin)
                    <div class="card-body border-bottom">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('ui.secret_archive_new_pin') }}</label>
                                <input wire:model.defer="newFolderPin" type="password" inputmode="numeric" pattern="[0-9]*" autocomplete="new-password" class="form-control @error('newFolderPin') is-invalid @enderror">
                                @error('newFolderPin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <button wire:click="changeFolderPin({{ $activeFolder->id }})" type="button" class="btn btn-label-warning w-100">
                                    <i class="ti ti-key me-1"></i>{{ __('ui.secret_archive_change_pin') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
                @if($canManageCurrentAccount)
                    <div class="card-body border-bottom">
                        <div class="archive-upload-panel">
                            <div class="archive-upload-heading">
                                <span class="archive-upload-icon" aria-hidden="true"><i class="ti ti-cloud-upload"></i></span>
                                <div>
                                    <div class="fw-semibold">{{ __('ui.secret_archive_upload_file') }}</div>
                                    <div class="small text-muted">{{ __('ui.secret_archive_file_types') }}</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <label for="secret-archive-file-input" class="btn btn-label-primary mb-0">
                                    <i class="ti ti-file-plus me-1"></i>{{ __('ui.secret_archive_choose_file') }}
                                </label>
                                <input id="secret-archive-file-input" wire:model="archiveFiles" type="file" multiple accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.mp4,.mov,.avi,.mkv,.webm,.pdf,.doc,.docx,.xls,.xlsx,.txt" class="visually-hidden">
                                <div wire:loading wire:target="archiveFiles" class="small text-muted">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('ui.uploading') }}
                                </div>
                            </div>

                            @error('archiveFiles')<div class="text-danger small">{{ $message }}</div>@enderror

                            @if($archiveFiles !== [])
                                <div class="archive-selected-files">
                                    @foreach($archiveFiles as $index => $archiveFile)
                                        @error('archiveFiles.'.$index)<div class="text-danger small">{{ $message }}</div>@enderror
                                        <div class="archive-selected-file" wire:key="archive-selected-{{ $archiveFile->getFilename() }}">
                                            <i class="ti ti-file archive-selected-file-icon" aria-hidden="true"></i>
                                            <span class="archive-selected-file-name">{{ $archiveFile->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="removeFile({{ $index }})" class="archive-selected-file-remove" title="{{ __('ui.remove') }}" aria-label="{{ __('ui.remove') }}">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex justify-content-end">
                                <button wire:click="uploadFiles" wire:loading.attr="disabled" type="button" class="btn btn-primary" @disabled($archiveFiles === [])>
                                    <i class="ti ti-cloud-upload me-1"></i>{{ __('ui.secret_archive_upload') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('ui.name') }}</th>
                                <th class="text-center">{{ __('ui.type') }}</th>
                                <th class="text-center">{{ __('ui.size') }}</th>
                                <th>{{ __('ui.date') }}</th>
                                <th class="text-center">{{ __('ui.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeFiles as $file)
                                @php
                                    $fileIcon = str_starts_with($file->mime, 'image/') ? 'ti-photo' : (str_starts_with($file->mime, 'video/') ? 'ti-video' : ($file->mime === 'application/pdf' ? 'ti-file-type-pdf' : 'ti-file'));
                                @endphp
                                <tr wire:key="secret-file-{{ $file->id }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="archive-file-icon" aria-hidden="true"><i class="ti {{ $fileIcon }}"></i></span>
                                            <div class="text-break fw-semibold">{{ $file->original_name }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center"><small class="text-muted">{{ $file->mime }}</small></td>
                                    <td class="text-center text-nowrap">{{ $this->formatFileSize((int) $file->size) }}</td>
                                    <td class="text-nowrap">{{ $file->created_at?->translatedFormat('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <div class="archive-file-actions">
                                            <a href="{{ route('secret-archive-files.view', $file->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('ui.open_document') }}" aria-label="{{ __('ui.open_document') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('secret-archive-files.download', $file->id) }}" class="btn btn-sm btn-icon btn-label-success" title="{{ __('ui.download') }}" aria-label="{{ __('ui.download') }}">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            @if($confirmedDeleteFileId === $file->id)
                                                <button wire:click="deleteFile({{ $file->id }})" type="button" class="btn btn-sm btn-danger">{{ __('ui.confirm') }}</button>
                                            @elseif($canManageCurrentAccount)
                                                <button wire:click="confirmDeleteFile({{ $file->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('ui.secret_archive_delete_file') }}" aria-label="{{ __('ui.secret_archive_delete_file') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="ti ti-folder-open d-block fs-2 mb-2"></i>{{ __('ui.secret_archive_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
