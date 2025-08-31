<aside class="footer container text-center small pt-3 pb-5">
    <div>
        @lang('linkace.project_of') <a href="https://woblick.dev/?utm_source=linkace" rel="noopener" target="_blank">Woblick.dev</a>
        @if(systemsettings('additional_footer_link_url') && systemsettings('additional_footer_link_text'))
            | <a href="{{ systemsettings('additional_footer_link_url') }}" rel="noreferrer noopener" target="_blank">
                {{ systemsettings('additional_footer_link_text') }}
            </a>
        @endif
        @if(systemsettings('contact_page_enabled'))
            | <a href="{{ route('contact') }}">
                {{ systemsettings('contact_page_title') ?? trans('linkace.contact') }}
            </a>
        @endif
    </div>
    @auth
        <div class="mt-1">
            @lang('linkace.version', ['version' => \App\Helper\UpdateHelper::currentVersion()]) -
            <x-update-check class="d-inline-block"/>
        </div>
    @endauth
</aside>
