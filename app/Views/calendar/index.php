<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>
Calendar & Tasks
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Pending Tasks</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="pendingTasksList" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($reminders)): ?>
                        <li class="list-group-item text-muted text-center">No tasks found.</li>
                    <?php else: ?>
                        <?php foreach ($reminders as $reminder): ?>
                            <?php if ($reminder['status'] === 'pending'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div style="max-width: 70%;">
                                        <strong class="d-block text-truncate"><?= esc($reminder['title']) ?></strong>
                                        <small class="text-muted"><i class="far fa-calendar-alt"></i> <?= date('d M, Y', strtotime($reminder['reminder_date'])) ?></small>
                                    </div>
                                    <span class="badge bg-<?= $reminder['priority'] === 'high' ? 'danger' : ($reminder['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($reminder['priority']) ?>
                                    </span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card card-primary">
            <div class="card-body p-0">
                <div id="calendar" style="min-height: 600px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task / Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addReminderForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="reminder_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time (Optional)</label>
                            <input type="time" name="reminder_time" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Reminder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="eventDescription" class="mb-3"></p>
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                    <div>
                        <i class="far fa-clock text-muted me-1"></i>
                        <span id="eventDate" class="text-muted small"></span>
                    </div>
                    <span id="eventPriority" class="badge"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger btn-sm" id="deleteEventBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button type="button" class="btn btn-success btn-sm" id="completeEventBtn">
                    <i class="fas fa-check"></i> Mark Completed
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var currentEventId = null;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        themeSystem: 'bootstrap5',
        events: '<?= base_url('calendar/fetch') ?>',
        eventClick: function(info) {
            currentEventId = info.event.id;
            $('#eventTitle').text(info.event.title);
            $('#eventDescription').text(info.event.extendedProps.description || 'No description provided.');
            
            // Format date nicely
            let dateStr = info.event.start.toLocaleDateString('en-IN', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            $('#eventDate').text(dateStr);
            
            var badgeClass = 'bg-info';
            var priorityText = 'Low';
            if (info.event.backgroundColor === '#dc3545') { badgeClass = 'bg-danger'; priorityText = 'High'; }
            else if (info.event.backgroundColor === '#ffc107') { badgeClass = 'bg-warning text-dark'; priorityText = 'Medium'; }
            
            $('#eventPriority').attr('class', 'badge ' + badgeClass).text(priorityText + ' Priority');
            
            if (info.event.extendedProps.status === 'completed') {
                $('#completeEventBtn').hide();
            } else {
                $('#completeEventBtn').show();
            }
            
            $('#eventDetailsModal').modal('show');
        }
    });
    calendar.render();

    // Handle Form Submit
    $('#addReminderForm').on('submit', function(e) {
        e.preventDefault();
        $.post('<?= base_url('calendar/store') ?>', $(this).serialize(), function(response) {
            if (response.success) {
                $('#addReminderModal').modal('hide');
                calendar.refetchEvents();
                location.reload(); 
            } else {
                alert(response.message);
            }
        });
    });

    // Handle Complete
    $('#completeEventBtn').on('click', function() {
        $.post('<?= base_url('calendar/update/') ?>' + currentEventId, {
            status: 'completed',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function(response) {
            if (response.success) {
                $('#eventDetailsModal').modal('hide');
                calendar.refetchEvents();
                location.reload();
            }
        });
    });

    // Handle Delete
    $('#deleteEventBtn').on('click', function() {
        if (confirm('Are you sure you want to delete this task?')) {
            $.post('<?= base_url('calendar/delete/') ?>' + currentEventId, {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(response) {
                if (response.success) {
                    $('#eventDetailsModal').modal('hide');
                    calendar.refetchEvents();
                    location.reload();
                }
            });
        }
    });
});
</script>
<style>
    .fc-event { cursor: pointer; border: none; }
    .fc-daygrid-event { padding: 2px 5px; }
    .transaction-item:hover, .payment-item:hover {
        cursor: pointer;
        filter: brightness(0.95);
    }
</style>
<?= $this->endSection() ?>
