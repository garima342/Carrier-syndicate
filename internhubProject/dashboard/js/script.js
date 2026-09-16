document.addEventListener('DOMContentLoaded', function () {

  // ---------------------------------------------------------------
  // Logo upload (AJAX, no page reload) — sidebar
  // ---------------------------------------------------------------
  var logoInput = document.getElementById('logoInput');
  if (logoInput) {
    logoInput.addEventListener('change', function () {
      if (!this.files || !this.files[0]) return;

      var formData = new FormData();
      formData.append('logo', this.files[0]);

      fetch('logo-upload.php', { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data.success) {
            var mark = document.getElementById('logoMark');
            mark.style.backgroundImage = "url('" + data.path + "')";
            mark.style.backgroundSize = 'cover';
            mark.style.backgroundPosition = 'center';
            mark.textContent = '';
            // Also update topbar logo if present
            var topImg = document.querySelector('.topbar-logo img');
            if (topImg) {
              topImg.src = data.path;
            } else {
              var topLogo = document.querySelector('.topbar-logo');
              if (topLogo) {
                topLogo.innerHTML = '<img src="' + data.path + '" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
              }
            }
          } else {
            alert(data.error || 'Upload failed.');
          }
        })
        .catch(function () { alert('Upload failed. Please try again.'); });
    });
  }

  // ---------------------------------------------------------------
  // Notification bell dropdown
  // ---------------------------------------------------------------
  var notifBtn      = document.getElementById('notifBtn');
  var notifDropdown = document.getElementById('notifDropdown');

  if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      notifDropdown.classList.toggle('hidden');

      if (!notifDropdown.classList.contains('hidden')) {
        fetch('api/notifications.php?action=list')
          .then(function (res) { return res.json(); })
          .then(function (items) {
            if (!items.length) {
              notifDropdown.innerHTML = '<div class="notif-empty">No notifications yet</div>';
            } else {
              notifDropdown.innerHTML = items.map(function (n) {
                return '<div class="notif-item ' + (n.is_read == 0 ? 'unread' : '') + '">' +
                  '<div>' + n.message + '</div>' +
                  '<div class="notif-time">' + n.created_at + '</div>' +
                  '</div>';
              }).join('');
            }

            // Mark everything read after showing
            fetch('api/notifications.php?action=mark_read', { method: 'POST' })
              .then(function () {
                var badge = document.getElementById('notifBadge');
                if (badge) badge.remove();
              });
          })
          .catch(function () {
            notifDropdown.innerHTML = '<div class="notif-empty">Could not load notifications.</div>';
          });
      }
    });

    document.addEventListener('click', function (e) {
      if (!notifBtn.contains(e.target)) {
        notifDropdown.classList.add('hidden');
      }
    });
  }

  // ---------------------------------------------------------------
  // Confirm before deleting rows (postings, sessions, slots)
  // ---------------------------------------------------------------
  document.querySelectorAll('.js-delete').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm('Delete this item? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // ---------------------------------------------------------------
  // Flash banners: auto-dismiss after 5 seconds
  // ---------------------------------------------------------------
  document.querySelectorAll('.flash-banner').forEach(function (banner) {
    setTimeout(function () {
      banner.style.transition = 'opacity 0.4s ease';
      banner.style.opacity = '0';
      setTimeout(function () { banner.remove(); }, 400);
    }, 5000);
  });

  // ---------------------------------------------------------------
  // Active nav: highlight the current page link
  // (CSS .active class is also set server-side for robustness)
  // ---------------------------------------------------------------
  var currentPath = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.side-nav a').forEach(function (link) {
    var linkPage = link.getAttribute('href').split('/').pop().split('?')[0];
    if (linkPage === currentPath) {
      link.classList.add('active');
    }
  });

});
