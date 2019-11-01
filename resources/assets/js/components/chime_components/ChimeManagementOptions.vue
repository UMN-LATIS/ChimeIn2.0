<template>
    <ul>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="requireLogin" id="requireLogin" v-model="require_login_local"
                    @change="$emit('update:require_login', $event.target.checked);">
                <label class="form-check-label" for="requireLogin">Require Login to Join or Access</label>
            </div>
        </li>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="studentView" id="studentView" v-model="students_can_view_local"
                    @change="$emit('update:students_can_view', $event.target.checked)">
                <label class="form-check-label" for="studentView">Participants can view results</label>
            </div>
        </li>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="joinInstructions" id="joinInstructions"
                    v-model="join_instructions_local" @change="$emit('update:join_instructions', $event.target.checked)">
                <label class="form-check-label" for="joinInstructions">Display "join" instructions when
                    presenting</label>
            </div>
        </li>
    </ul>
</template>

<style scoped>
ul li {
  list-style: none;
}
</style>

<script>

export default {
        data() {
            return {
                require_login_local: this.require_login,
                students_can_view_local: this.students_can_view,
                join_instructions_local: this.join_instructions
            }
        },
        props: ['require_login','students_can_view', 'join_instructions'],

}
</script>