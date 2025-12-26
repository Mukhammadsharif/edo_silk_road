@php($data = $journal->workEcpInfo($document->id, $signer->to_user)->docInfo->json_data)

<input value="{!! date('d.m.Y H:i:s', strtotime($journal->workEcpInfo($document->id, $signer->to_user)->signing_time))  !!}" id="time{{ $signer->id }}" hidden>
<input value="{!! json_decode($data)->serialNumber !!}" id="serialNumber{{$signer->id}}" hidden>
<input value="{!! substr(json_decode($data)->signature, 0 , 50) !!}" id="signature{{$signer->id}}" hidden>
<input value="{!! json_decode($data)->digest !!}" id="digest{{$signer->id}}" hidden>
<input value="{!! json_decode($data)->subjectName !!}" id="subjectName{{$signer->id}}" hidden>

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
