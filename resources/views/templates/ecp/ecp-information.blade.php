
@php $data = $journal->workEcpInfo($document->id, $doc->from_user)->docInfo->json_data @endphp

@php
    $signingTimeRaw = $journal->workEcpInfo($document->id, $doc->from_user)->signing_time ?? null;
    $formattedTime = null;

    if ($signingTimeRaw) {
        // Пробуем оба формата
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $signingTimeRaw)
            ?: DateTime::createFromFormat('Y.m.d H:i:s', $signingTimeRaw);

        if ($dt) {
            $formattedTime = $dt->format('d.m.Y H:i:s');
        }
    }
@endphp

<input value="{{ $formattedTime??'' }}" id="time{{ $doc->id }}" hidden>
<input value="{{ json_decode($data)->serialNumber }}" id="serialNumber{{$doc->id}}" hidden>
<input value="{{ substr(json_decode($data)->signature, 0 , 50) }}" id="signature{{$doc->id}}" hidden>
<input value="{{ json_decode($data)->digest }}" id="digest{{$doc->id}}" hidden>
<input value="{{ json_decode($data)->subjectName }}" id="subjectName{{$doc->id}}" hidden>

@include('templates.ecp.espInfoModal')

<script>
    function showEspInfo(id) {
        let time = document.querySelector(`#time${id}`).value
        let subjectName = document.querySelector(`#subjectName${id}`).value
        let digest = document.querySelector(`#digest${id}`).value
        let serialNumber = document.querySelector(`#serialNumber${id}`).value

        let info = getValue(subjectName,"CN")
        let registred = getValue(subjectName,"ST")+' '+getValue(subjectName,"L")

        $('.mh-class').removeClass("bg-yellow").addClass("bg-green");
        $('#confButton').val("confirm-order");
        $('#signingTime').html(time);
        $('#qr_signed_by').html(info);
        $('#registered').html(registred);
        $('#serialNumber').html(serialNumber);
        $('#digest').html(digest);
        $('#infoModel').modal('show');
    }

    function getValue(subject, field) {
        // Объект → сразу поле
        if (subject && typeof subject === 'object' && field in subject) {
            const val = subject[field];
            return val != null ? String(val).trim() : "-";
        }

        // Строка в формате DN
        if (typeof subject === 'string') {
            // Более надёжное регулярное выражение
            const regex = new RegExp(`(?:^|,)\\s*${escapeRegExp(field)}\\s*=\\s*([^,]+)`, 'i');
            const match = subject.match(regex);
            return match ? match[1].trim() : "-";
        }

        return "-";
    }

    // Вспомогательная функция для экранирования спецсимволов в regex
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
</script>
