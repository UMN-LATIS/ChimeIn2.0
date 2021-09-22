<template>
    <div class="row mt-2">
        <div class="col-12">
            <h1>Canvas/ChimeIn Setup</h1>
                <lti-import-selector :similar_chimes="ltiDetails.similar_chimes" v-if="ltiDetails.similar_chimes" v-on:new_chime="ltiDetails.similar_chimes = false"/>
                <lti-setup-preferences v-else/>
            

        </div>
    </div>
</template>

<script>
export default {
    props: ["lti_details"],
    data() {
        return {
            ltiDetails: this.lti_details
        }
    }
}
</script>