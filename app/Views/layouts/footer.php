<div style="text-align:center">
    <p class="footer-copyright">© Copyright <span id="year"></span> - Gowthamraj Somanathan</p>
</div>
</div>
</div>
<!-- Required Js -->
<script src="/public/assets/js/vendor-all.min.js"></script>
<script src="/public/assets/js/plugins/bootstrap.min.js"></script>
<script src="/public/assets/js/plugins/feather.min.js"></script>
<script src="/public/assets/js/pcoded.min.js"></script>
<script src="/public/assets/js/jquery-3.5.1.js"></script>
<script src="/public/assets/js/jquery.dataTables.min.js"></script>
<script src="/public/assets/js/flatpickr.js"></script>
<script>
    $('#year').text(new Date().getFullYear());
</script>
<script>
    $(document).ready(function() {
        $('#employee-table').DataTable({
            "paging": true,
            "searching": true,
            "info": true
        });
        $('#transportTable').DataTable({
            "paging": true,
            "searching": true,
            "info": true
        });
        $('#employees-table').DataTable({
            "paging": true,
            "searching": true,
            "info": true
        });
        $('#statementsTable').DataTable({
            "paging": true,
            "searching": true,
            "info": true
        });
    });
</script>
</body>

</html>