@props(['headers' => []])

<div class="data-table-wrapper">
    @if(isset($header) && trim($header) !== '')
        <div class="data-table-header">
            {{ $header }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="data-table">
            @if(count($headers) > 0)
                <thead>
                    <tr>
                        @foreach($headers as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody {{ $attributes }}>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer) && trim($footer) !== '')
        <div class="data-table-footer">
            {{ $footer }}
        </div>
    @endif
</div>
