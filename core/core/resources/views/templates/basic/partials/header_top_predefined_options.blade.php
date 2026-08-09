<div class="predefined-widgets d-flex gap-2 align-items-center justify-content-end py-2">
    {{-- Upload Prescription Button --}}
    @auth
    <a href="{{ route('user.prescription.index') }}" class="btn btn--base btn-sm d-inline-flex align-items-center gap-1">
        <i class="las la-file-medical"></i>@lang('Upload Prescription')
    </a>
    @else
    <a href="{{ route('user.login') }}" class="btn btn--base btn-sm d-inline-flex align-items-center gap-1">
        <i class="las la-file-medical"></i>@lang('Upload Prescription')
    </a>
    @endauth

    @php
    $socials = getContent('social_icon.element', orderById: true);
    @endphp
    @if ($socials->count() > 0)
    <ul class="social-icons d-flex gap-2 flex-wrap mb-0 list-unstyled header-social-icons">
        @foreach ($socials as $item)
        <li>
            <a href="{{ $item->data_values->url }}" target="_blank">
                @php echo $item->data_values->social_icon; @endphp
            </a>
        </li>
        @endforeach
    </ul>
    @endif

    @if ($headerOne->language_option == 'on')
    <div class="d-none d-lg-block">
        @include('Template::partials.menu.language_menu')
    </div>
    @endif


    @if ($headerOne->user_option == 'on')
    @include('Template::partials.user_auth_options')
    @endif
</div>