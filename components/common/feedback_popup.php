<?php if (!empty($feedback_enabled)): ?>
<div id="feedbackPopup" class="fixed bottom-6 right-6 z-50 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-xl p-5 w-80 hidden" style="display: none;">
    <button onclick="closeFeedback()" class="absolute top-3 right-3 text-on-surface-variant hover:text-on-surface">
        <span class="material-symbols-outlined text-sm">close</span>
    </button>
    <div class="flex items-center gap-3 mb-3">
        <span class="material-symbols-outlined text-primary">feedback</span>
        <h4 class="font-label-md text-on-surface text-sm font-bold">Quick Feedback</h4>
    </div>
    <p class="text-[11px] text-on-surface-variant mb-3 font-medium">Help us improve — share your thoughts!</p>
    <textarea id="feedbackText" rows="3" placeholder="Type your feedback..." class="w-full px-3 py-2 bg-surface border border-outline-variant rounded-xl text-xs focus:ring-2 focus:ring-primary outline-none resize-none font-semibold"></textarea>
    <button onclick="submitFeedback()" class="mt-3 w-full bg-primary text-on-primary py-2 rounded-xl font-label-md text-xs font-bold hover:opacity-90 transition-all">Submit</button>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('feedback_submitted')) {
        setTimeout(function() {
            var el = document.getElementById('feedbackPopup');
            if (el) el.style.display = 'block';
        }, 5000);
    }
});
</script>
<?php endif; ?>
