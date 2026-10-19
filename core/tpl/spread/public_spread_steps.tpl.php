<?php
/* Copyright (C) 2026 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/tpl/spread/public_spread_steps.tpl.php
 * \ingroup doliletter
 * \brief   Signature workflow of the spread public page, as a breadcrumb: who signs, reading of the
 *          object, validation and signature. Each step says how far it is (person, blocks read, date
 *          of signature) and brings to its part of the page. Pinned at the top of the screen once
 *          scrolled past: the page is long, and one must know at any time where they stand.
 *          Expects: $langs, $spreadSteps (built by add_spread.php).
 *          The parts of the page are marked by .spread-steps__anchor elements: spread-step-<step>.
 */

if (empty($spreadSteps)) {
    return;
}
?>
<style>
.spread-steps-holder { --spread-step-marker: 34px; margin: 16px 0; }
.spread-steps { padding: 14px 12px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
.spread-steps__list { display: flex; margin: 0; padding: 0; list-style: none; }
.spread-steps__step { position: relative; flex: 1 1 0; min-width: 0; }
/* Trait d'une pastille a la suivante, vert une fois l'etape franchie */
.spread-steps__step + .spread-steps__step::before { content: ''; position: absolute; top: calc(var(--spread-step-marker) / 2 - 1px); left: calc(-50% + var(--spread-step-marker) / 2 + 8px); right: calc(50% + var(--spread-step-marker) / 2 + 8px); height: 2px; background: #e2e8f0; border-radius: 1px; }
.spread-steps__step--done + .spread-steps__step::before { background: #16a34a; }
.spread-steps__link { display: flex; flex-direction: column; align-items: center; gap: 6px; width: 100%; margin: 0; padding: 0 4px; font: inherit; color: inherit; text-align: center; background: none; border: none; border-radius: 6px; cursor: pointer; }
.spread-steps__link:disabled { cursor: default; }
.spread-steps__link:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
.spread-steps__marker { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; width: var(--spread-step-marker); height: var(--spread-step-marker); box-sizing: border-box; font-size: 15px; font-weight: 700; color: #94a3b8; background: #fff; border: 2px solid #cbd5e1; border-radius: 50%; transition: background 0.2s, border-color 0.2s; }
.spread-steps__marker .fa-check { display: none; }
.spread-steps__step--current .spread-steps__marker { color: #fff; background: #2563eb; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
.spread-steps__step--done .spread-steps__marker { color: #fff; background: #16a34a; border-color: #16a34a; }
.spread-steps__step--done .spread-steps__number { display: none; }
.spread-steps__step--done .spread-steps__marker .fa-check { display: inline; }
.spread-steps__text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.spread-steps__label { font-size: 13px; font-weight: 700; line-height: 1.3; color: #64748b; }
.spread-steps__step--current .spread-steps__label { color: #1d4ed8; }
.spread-steps__step--done .spread-steps__label { color: #166534; }
.spread-steps__link:not(:disabled):hover .spread-steps__label { text-decoration: underline; }
.spread-steps__status { font-size: 12px; line-height: 1.3; color: #64748b; overflow-wrap: anywhere; }
.spread-steps__status:empty { display: none; }
/* Une etape atteinte depuis la barre epinglee arrive sous elle, pas dessous */
.spread-steps__anchor { scroll-margin-top: 72px; }

/* Barre epinglee : une ligne, pastilles et intitules seulement */
.spread-steps--pinned { position: fixed; top: 0; left: 50%; z-index: 100; width: 100%; max-width: 900px; padding: 8px 16px; box-sizing: border-box; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12); transform: translateX(-50%); }
.spread-steps--pinned .spread-steps__step { display: flex; align-items: center; flex: 0 1 auto; }
.spread-steps--pinned .spread-steps__step + .spread-steps__step { flex: 1 1 auto; }
.spread-steps--pinned .spread-steps__step + .spread-steps__step::before { position: static; flex: 1 1 auto; min-width: 12px; margin: 0 10px; }
.spread-steps--pinned .spread-steps__link { flex-direction: row; gap: 8px; width: auto; padding: 0; text-align: left; white-space: nowrap; }
.spread-steps--pinned .spread-steps__marker { width: 26px; height: 26px; font-size: 13px; }
.spread-steps--pinned .spread-steps__step--current .spread-steps__marker { box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
.spread-steps--pinned .spread-steps__status { display: none; }

@media (max-width: 600px) {
    .spread-steps-holder { --spread-step-marker: 30px; }
    .spread-steps { padding: 12px 4px; }
    .spread-steps__label { font-size: 12px; }
    .spread-steps__status { font-size: 11px; }
    .spread-steps--pinned { padding: 8px 12px; border-radius: 0; }
    /* Trois intitules ne tiennent pas sur une ligne : seul celui de l'etape en cours reste */
    .spread-steps--pinned .spread-steps__step:not(.spread-steps__step--current) .spread-steps__text { display: none; }
}

@media (prefers-reduced-motion: reduce) {
    .spread-steps__marker { transition: none; }
}
</style>

<div class="spread-steps-holder">
    <nav class="spread-steps" aria-label="<?php echo dol_escape_htmltag($langs->transnoentities('SpreadStepsTitle')); ?>" data-mode="<?php echo dol_escape_htmltag($spreadSteps['mode']); ?>" data-signed="<?php echo (int) $spreadSteps['signed']; ?>" data-name="<?php echo dol_escape_htmltag($spreadSteps['name']); ?>" data-texts="<?php echo dol_escape_htmltag(json_encode($spreadSteps['texts'])); ?>">
        <ol class="spread-steps__list">
            <?php $spreadStepNumber = 0;
            foreach ($spreadSteps['steps'] as $spreadStepCode => $spreadStep) {
                $spreadStepNumber++; ?>
            <li class="spread-steps__step spread-steps__step--<?php echo $spreadStep['state']; ?>" data-step="<?php echo $spreadStepCode; ?>"<?php echo ($spreadStep['state'] == 'current') ? ' aria-current="step"' : ''; ?>>
                <button type="button" class="spread-steps__link" data-target="spread-step-<?php echo $spreadStepCode; ?>">
                    <span class="spread-steps__marker" aria-hidden="true"><span class="spread-steps__number"><?php echo $spreadStepNumber; ?></span><i class="fas fa-check"></i></span>
                    <span class="spread-steps__text">
                        <span class="spread-steps__label"><?php echo dol_escape_htmltag($spreadStep['label']); ?></span>
                        <span class="spread-steps__status"><?php echo dol_escape_htmltag($spreadStep['status']); ?></span>
                    </span>
                </button>
            </li>
            <?php } ?>
        </ol>
    </nav>
</div>

<script>
/**
 * Steps of the signature workflow: follow the progress made on the page without reloading it, bring
 * to the part of the page of a step, and keep the bar in sight while scrolling.
 */
window.spreadSteps = {
    init: function() {
        var nav = $('.spread-steps');
        if (!nav.length) {
            return;
        }

        // A button, not a link to #spread-step-x: a hash in the address would end up in the middle
        // of the URLs the page builds from document.URL for its AJAX calls
        nav.find('.spread-steps__link').each(function() {
            $(this).prop('disabled', !document.getElementById($(this).data('target')));
        });
        nav.on('click', '.spread-steps__link', function() {
            var target = document.getElementById($(this).data('target'));
            if (target) {
                target.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        });

        // Documents uploaded, signatories added or completed, answers saved: all of it goes through AJAX
        $(document).on('ajaxComplete', window.spreadSteps.refresh);

        window.spreadSteps.pin(nav);
        window.spreadSteps.refresh();
    },

    /**
     * Pin the bar at the top of the screen once its place has been scrolled past. Its place keeps
     * the height of the bar, so the page does not jump when the bar leaves the flow.
     */
    pin: function(nav) {
        var holder = nav.parent('.spread-steps-holder');
        if (!holder.length || !('IntersectionObserver' in window)) {
            return;
        }

        new IntersectionObserver(function(entries) {
            var pinned = !entries[0].isIntersecting && entries[0].boundingClientRect.top < 0;
            if (pinned === nav.hasClass('spread-steps--pinned')) {
                return;
            }

            holder.css('min-height', pinned ? nav.outerHeight() : '');
            nav.toggleClass('spread-steps--pinned', pinned);
        }).observe(holder[0]);
    },

    /**
     * State of each step from what the page holds now. Same rule as doliletter_spread_get_step_states():
     * a step is only done once the ones before it are.
     */
    refresh: function() {
        var nav = $('.spread-steps');
        // Nothing on this page completes a registration, and a signature is final
        if (!nav.length || nav.data('mode') === 'register' || nav.data('signed')) {
            return;
        }

        var texts  = nav.data('texts');
        var isSign = nav.data('mode') === 'sign';
        // For a user adding signatories, the first person of the list left to sign: the one the
        // bottom sign button opens
        var person = isSign ? $('.pp-single-person') : $('.user-signatures-list .user-signature-item.signature-not-validated').not('.user-signature-item--new').first();
        var done   = {who: false, read: false, sign: false};
        var status = {};

        // 1. Who signs: someone identified, every mandatory document provided or declared not applicable
        var pendingDocuments = window.spreadSteps.countPendingDocuments(person.find('.pp-cert-upload'));
        if (!isSign && !person.length) {
            status.who = texts.whoAdd;
        } else if (pendingDocuments > 0) {
            status.who = texts.documents.replace('%s', pendingDocuments);
        } else if (!isSign && !person.find('.sign-btn').not('.button-disable').length) {
            status.who = texts.whoIncomplete;
        } else {
            done.who   = true;
            // attr() and not data(): jQuery would turn a phone number into a number and drop its leading 0
            status.who = isSign ? nav.attr('data-name') : window.spreadSteps.getName(person);
        }

        // 2. Reading: every block taken note of. An object without any has nothing to tick.
        var blocks     = $('.pp-risk-ack');
        var blocksRead = blocks.filter('.pp-risk-ack--done').length;
        done.read      = blocksRead === blocks.length;
        if (blocks.length && texts.read) {
            status.read = texts.read.replace('%s', blocksRead).replace('%s', blocks.length);
        }

        // 3. Validation and signature: only the signature itself completes it, and it reloads the page
        var states  = window.spreadSteps.getStates(done);
        status.sign = (states.sign === 'current') ? texts.toSign : '';

        $.each(states, function(step, state) {
            var item = nav.find('.spread-steps__step[data-step="' + step + '"]');
            item.removeClass('spread-steps__step--done spread-steps__step--current spread-steps__step--todo').addClass('spread-steps__step--' + state);
            item.attr('aria-current', (state === 'current') ? 'step' : null);
            if (status[step] !== undefined) {
                item.find('.spread-steps__status').text(status[step]);
            }
        });
    },

    /**
     * Done, current (the first step not done) or todo, for each step in order.
     */
    getStates: function(done) {
        var states       = {};
        var previousDone = true;
        var hasCurrent   = false;

        $.each(done, function(step, isDone) {
            isDone = previousDone && isDone;
            if (isDone) {
                states[step] = 'done';
            } else if (!hasCurrent) {
                states[step] = 'current';
                hasCurrent   = true;
            } else {
                states[step] = 'todo';
            }
            previousDone = isDone;
        });

        return states;
    },

    /**
     * Mandatory documents still waiting for an answer: no photo, no file, not declared not applicable.
     * The counters of the Saturne media blocks are rendered again after each upload.
     */
    countPendingDocuments: function(items) {
        var pending = 0;

        items.each(function() {
            var item    = $(this);
            var hasFile = item.find('.saturne-media-count-badge').filter(function() {
                return parseInt($(this).text(), 10) > 0;
            }).length > 0;

            if (item.find('.pp-cert-badge-mandatory').length && !item.hasClass('pp-cert-upload--not-concerned') && !hasFile) {
                pending++;
            }
        });

        return pending;
    },

    /**
     * Name of the signatory of a row of the list, as it is being typed for an external one.
     */
    getName: function(row) {
        if (!row.find('.external-signatory-input').length) {
            return row.attr('data-signatory-name') || '';
        }

        var name = $.trim((row.find('[data-field="first_name"]').val() || '') + ' ' + (row.find('[data-field="last_name"]').val() || ''));

        return name || row.find('[data-field="email"]').val() || row.find('[data-field="phone"]').val() || '';
    }
};

$(document).ready(window.spreadSteps.init);
</script>
