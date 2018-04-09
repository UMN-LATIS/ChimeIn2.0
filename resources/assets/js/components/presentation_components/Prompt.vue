<template>
    <div class="card">
        <div
            class="card-content"
            v-if="question"
            v-bind:class="{
                in_progress: (
                    session
                    && (session.in_progress && session.in_progress != '0')
                    && session.question_id == question.id)
            }">
            <div class="card">
                <div class="card-content">
                    <p class="flow-text" v-html="question.text"></p>
                </div>
            </div>

            <multiple-choice-display
                v-if="question.question_info.question_type === 'multiple_choice'"
                :question="question">
            </multiple-choice-display>
            
            <div v-else>
                Free Response
            </div>
        </div>
        <div class="card-content" v-else>
            <span class="card-title">
                <h4>'No Question Yet!'</h4>
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: ['question', 'session'],
}
</script>

<style>
    li {
        font-size: 1.5em;
    }
</style>

