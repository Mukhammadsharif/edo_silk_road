{{--
 | Просмотр одного вложения.
 |
 | Подключать так:
 |     @include('templates.file-viewer', ['file' => $file])
 |
 | Для файлов ответных писем маршруты другие:
 |     @include('templates.file-viewer', [
 |         'file'          => $file,
 |         'previewRoute'  => 'r-file.pre-view',
 |         'downloadRoute' => 'r-file.download',
 |     ])
 |
 | Что рисуется:
 |   картинка        — <img>
 |   pdf             — <iframe> со встроенным просмотрщиком браузера
 |   word/excel/ppt  — OnlyOffice только на чтение, по ширине страницы
 |   текст           — содержимое файла в <pre>
 |   звук/видео      — <audio> / <video>
 |
 | Файл, который показать нечем (exe, zip, rar и прочее), не рисуется
 | ВООБЩЕ — ни карточки, ни заглушки. Он и так виден в боковой панели
 | «Прикреплённые файлы» со ссылкой на скачивание, а пустой блок с
 | надписью «предпросмотр недоступен» только занимал место.
 |
 | Офисный просмотрщик и текст загружаются сами, когда карточка
 | появляется в поле зрения (IntersectionObserver в file-viewer-js).
 --}}

@php
    use App\Support\FilePreview;

    $previewRoute  = $previewRoute  ?? 'file.pre-view';
    $downloadRoute = $downloadRoute ?? 'file.download';

    $kind = FilePreview::kind($file->file_ext);

    /* Конфигурация OnlyOffice нужна до отрисовки: если сервис не
       настроен, офисный файл показать нечем, и карточки тоже не будет. */
    $officeConfig = null;
    if ($kind === FilePreview::OFFICE) {
        $viewer = app(\App\Services\OnlyOfficeViewerService::class);
        if ($viewer->isEnabled()) {
            $officeConfig = $viewer->configFor($file);
        }
    }

    $canShow = $kind !== FilePreview::NONE
        && ($kind !== FilePreview::OFFICE || $officeConfig !== null);

    /* __() при отсутствующем ключе возвращает САМ КЛЮЧ, а не null,
       поэтому привычное ?? 'запасной текст' не срабатывает: на странице
       появляется «message.preview_unavailable». Сравниваем явно. */
    $tr = function (string $key, string $default): string {
        $value = __($key);
        return is_string($value) && $value !== $key ? $value : $default;
    };
@endphp

@if($canShow)
    @php
        $icon = FilePreview::icon($file->file_ext);

        $previewUrl  = route($previewRoute,  ['id' => $file->id]);
        $downloadUrl = route($downloadRoute, ['id' => $file->id]);

        $bytes = (int) ($file->file_size ?? 0);
        $sizeLabel = $bytes >= 1048576
            ? number_format($bytes / 1048576, 1) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';

        // Текст подгружаем сам, только если файл небольшой:
        // журнал на десяток мегабайт повесил бы страницу.
        $textAutoLoad = $kind === FilePreview::TEXT && $bytes > 0 && $bytes <= 262144;

        $viewerId = 'fv-' . $file->id;
    @endphp

    <div class="file-viewer-card" data-kind="{{ $kind }}">

        <div class="file-viewer-head">
            <div class="file-viewer-title">
                <i data-lucide="{{ $icon }}"></i>
                <span class="file-viewer-name" title="{{ $file->file_name }}">{{ $file->file_name }}</span>
                <span class="file-viewer-size">{{ $sizeLabel }}</span>
            </div>

            <div class="file-viewer-actions">
                @if(FilePreview::isInline($file->file_ext))
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                       class="file-viewer-btn" title="{{ $tr('action.open', 'Открыть в новой вкладке') }}">
                        <i data-lucide="external-link"></i>
                    </a>
                @endif
                <a href="{{ $downloadUrl }}" class="file-viewer-btn"
                   title="{{ $tr('action.download', 'Скачать') }}">
                    <i data-lucide="download"></i>
                </a>
            </div>
        </div>

        <div class="file-viewer-body">
            @switch($kind)

                @case(FilePreview::IMAGE)
                    <img src="{{ $previewUrl }}" alt="{{ $file->file_name }}"
                         class="file-viewer-image" loading="lazy">
                    @break

                @case(FilePreview::PDF)
                    <iframe src="{{ $previewUrl }}" class="file-viewer-frame" loading="lazy"></iframe>
                    @break

                @case(FilePreview::OFFICE)
                    <div class="file-viewer-office">
                        {{-- Сюда DocsAPI.DocEditor подставит свой iframe.
                             Содержимое ниже видно только до загрузки. --}}
                        <div class="file-viewer-office-mount"
                             id="{{ $viewerId }}"
                             data-office-auto="1">
                            <div class="file-viewer-loading">
                                <span class="file-viewer-spinner" aria-hidden="true"></span>
                                <span>{{ $tr('message.loading', 'Загрузка документа…') }}</span>
                            </div>
                        </div>

                        <script type="application/json" id="{{ $viewerId }}-config">
                            {!! json_encode($officeConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                        </script>
                    </div>
                    @break

                @case(FilePreview::TEXT)
                    <pre class="file-viewer-text"
                         id="{{ $viewerId }}"
                         data-text-src="{{ $previewUrl }}"
                         @if($textAutoLoad) data-text-auto="1" @endif
                    ></pre>

                    @unless($textAutoLoad)
                        {{-- Крупный файл: грузим только по требованию --}}
                        <button type="button" class="file-viewer-open-btn"
                                data-text-open="{{ $viewerId }}">
                            <i data-lucide="eye"></i>
                            <span>{{ $tr('action.preview', 'Посмотреть') }}</span>
                        </button>
                    @endunless
                    @break

                @case(FilePreview::AUDIO)
                    <audio src="{{ $previewUrl }}" controls preload="none" class="file-viewer-audio"></audio>
                    @break

                @case(FilePreview::VIDEO)
                    <video src="{{ $previewUrl }}" controls preload="none" class="file-viewer-video"></video>
                    @break
            @endswitch
        </div>
    </div>
@endif
