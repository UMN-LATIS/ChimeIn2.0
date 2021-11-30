export const questionsListener = {
  data() {
    return {
      folder: { student_view: false },
      questions: [],
      usersCount: 0,
    };
  },
  methods: {
    get_chime: function () {
      var chimeId;
      if (this.chimeId) {
        chimeId = this.chimeId;
      } else {
        chimeId = this.chime.id;
      }
      return chimeId;
    },
    load_questions: function () {
      var folderId;
      if (this.folderId) {
        folderId = this.folderId;
      } else {
        folderId = this.folder.id;
      }

      const url =
        "/api/chime/" + this.get_chime() + "/folder/" + folderId + "/true";

      return axios
        .get(url)
        .then((res) => {
          this.folder = res.data;
          this.questions = res.data.questions.sort((a, b) => a.order - b.order);
        })
        .catch((err) => {
          this.$store.commit(
            "message",
            "Could not load questions. You may not have permission to view this page. "
          );
          console.log(err);
        });
    },
  },
  mounted() {
    Echo.join("session-status." + this.get_chime())
      .here((users) => {
        this.usersCount = users.length;
      })
      .listen("StartSession", (m) => {
        for (var question of this.questions) {
          if (question.id == m.session.question.id) {
            question.current_session_id = m.session.id;
            m.session.responses = [];
            question.sessions.push(m.session);
          }
        }
      })
      .listen("EndSession", (m) => {
        for (var question of this.questions) {
          if (question.id == m.session.question_id) {
            question.current_session_id = null;
          }
        }
      })
      .joining(() => {
        this.usersCount = this.usersCount + 1;
      })
      .leaving(() => {
        this.usersCount = this.usersCount - 1;
      });

    Echo.private("session-response." + this.get_chime()).listen(
      "SubmitResponse",
      (m) => {
        // none of this code is right but I'm having trouble thinking of the right way to do it.
        var targetSession = null;
        this.questions.forEach((question) => {
          question.sessions.forEach((session) => {
            if (session.id == m.session.id) {
              targetSession = session;
            }
          });
        });

        if (!targetSession) {
          console.log("Session does not exist.  Weird");
          console.log(m);
          return;
        }

        // eslint-disable-next-line no-prototype-builtins
        if (!targetSession.hasOwnProperty("responses")) {
          targetSession.responses = new Array();
        }

        var updateInPlace = false;
        if (m.isEdit) {
          targetSession.responses.forEach((response, index) => {
            if (response.id == m.response.id) {
              this.$set(targetSession.responses, index, m.response);
              updateInPlace = true;
            }
          });
        }

        if (!updateInPlace) {
          targetSession.responses.push(m.response);
        }
      }
    );
  },
  beforeDestroy: function () {
    Echo.leave("session-response." + this.get_chime());
    Echo.leave("session-status." + this.get_chime());
  },
};
