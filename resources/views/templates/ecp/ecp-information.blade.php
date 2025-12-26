
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

    function getValue(s, f) {
        var res = s.split(',');
        for (var i in res) {
            var n = res[i].search(f + "=");
            if (n !== -1) {
                return res[i].slice(n + f.length + 1);
            }
        }
        return "-";
    }
</script>
