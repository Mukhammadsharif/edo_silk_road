<!-- Модальное окно одобрения -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="ik ik-check-circle"></i> Obunani tasdiqlash</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <i class="ik ik-thumbs-up display-4 text-success mb-3"></i>
                <h5><strong id="approveOrgName"></strong></h5>
                <p>tashkiloti uchun <strong id="approveTariffName"></strong> tarifiga obuna so'rovini</p>
                <p class="text-success fw-bold">tasdiqlamoqchimisiz?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-dismiss="modal">Bekor qilish</button>
                <form id="approveForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg px-5">Ha, tasdiqlash</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно отклонения -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ik ik-x-circle"></i> Obunani rad etish</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <i class="ik ik-alert-octagon display-4 text-danger mb-3"></i>
                <h5><strong id="rejectOrgName"></strong></h5>
                <p>tashkiloti uchun <strong id="rejectTariffName"></strong> tarifiga obuna so'rovini</p>
                <p class="text-danger fw-bold">rad etmoqchimisiz?</p>
                <p class="text-muted small">Bu amalni keyincha qaytarib bo'lmaydi.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-dismiss="modal">Bekor qilish</button>
                <form id="rejectForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg px-5">Ha, rad etish</button>
                </form>
            </div>
        </div>
    </div>
</div>
