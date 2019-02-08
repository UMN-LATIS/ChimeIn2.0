export const questionsListener = {
    data() {
        return {
            folder: { student_view: false},
            questions: []
        }
    },
  methods: {
    load_questions: function() {
        var chimeId;
        if(this.chimeId) {
            chimeId = this.chimeId;
        }
        else {
            chimeId = this.chime.id;
        }

        var folderId;
        if(this.folderId) {
            folderId = this.folderId;
        }
        else {
            folderId = this.folder.id;
        }

        const url = (
            '/api/chime/'
            + chimeId
            + '/folder/'
            + folderId
            + '/true'
            );

        axios.get(url)
        .then(res => {                
            this.folder = res.data;
            this.questions = res.data.questions.sort((a,b) => a.order - b.order);
            console.log(this.questions);
            })
        .catch(err => {
            console.log(err);
        });
    }
},
mounted(){
    var chimeId;
    if(this.chimeId) {
        chimeId = this.chimeId;
    }
    else {
        chimeId = this.chime.id;
    }

    var self=this;
    Echo.private('session-status.' + chimeId)
    .listen('StartSession', m => {
        for(var question of this.questions) {
            if(question.id == m.session.question.id) {
                question.current_session_id = m.session.id;
                m.session.responses = [];
                question.sessions.push(m.session);
            }
        }
    })
    .listen('EndSession', m => {
        for(var question of this.questions) {
            if(question.id == m.session.question_id) {
                question.current_session_id = null;
            }
        }
    });

    Echo.private('session-response.'+ chimeId)
    .listen('SubmitResponse', m => {
            // none of this code is right but I'm having trouble thinking of the right way to do it.
            var targetSession = null;
            this.questions.forEach(question => {
                question.sessions.forEach(session => {
                    if(session.id == m.session.id) {
                        targetSession = session;
                    }
                })
            });

            if(!targetSession) {
                console.log("Session does not exist.  Weird");
                console.log(m);
                return;
            }

            if(!targetSession.hasOwnProperty('responses')) {
                targetSession.responses = new Array();
            }

            var updateInPlace = false;
            if(m.isEdit) {
                targetSession.responses.forEach((response, index) => {
                    if(response.id == m.response.id) {
                        Vue.set(targetSession.responses, index, m.response);
                        updateInPlace = true;
                    }
                });

            }
            
            if(!updateInPlace) {
                targetSession.responses.push(m.response);    
            }
        });
},
beforeDestroy: function() {
    var chimeId;
    if(this.chimeId) {
        chimeId = this.chimeId;
    }
    else {
        chimeId = this.chime.id;
    }
    Echo.leave('session-response.'+ chimeId);
    Echo.leave('session-status.'+ chimeId);
}
};