<template>
  <div class="table-container">
    <table class="table">
      <thead>
        <tr class="text-xs uppercase">
          <th scope="col"></th>
          <th
            v-for="(question, index) in questions"
            :key="question.id"
            scope="col"
            class="text-center"
          >
            Q{{ (index as number) + 1 }}
          </th>
          <th scope="col" class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        <UserParticipationRow
          v-for="user in users"
          :key="user.id"
          :user="user"
          :questions="questions"
          :responses="getResponsesForUser(user.id, responses)"
          :numberOfActiveQuestions="numberOfActiveQuestions"
          :valueForIncorrect="valueForIncorrect"
        />
      </tbody>
    </table>
  </div>
</template>
<script setup lang="ts">
import getResponsesForUser from "./getResponsesForUser";
import {
  User,
  Question,
  ChimeFolderParticipationResponseItem,
} from "../../types";
import UserParticipationRow from "./UserParticipationRow.vue";

defineProps<{
  users: User[];
  questions: Question[];
  responses: ChimeFolderParticipationResponseItem[];
  numberOfActiveQuestions: number;
  valueForIncorrect: number;
}>();
</script>
<style scoped>
.table-container {
  border: 1px solid #ddd;
  background: #fff;
  width: fit-content;
  min-width: 40rem;
  max-width: 100%;
  overflow: scroll;
  max-height: 50vh;
}
.table {
  margin-bottom: 0;
}

.table thead {
  position: sticky;
  top: 0;
  background: #fafafa;
  z-index: 10;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.table thead th:first-child {
  position: sticky;
  left: 0;
  background: #f3f3f3;
}

.table th {
  border: 0;
}

::-webkit-scrollbar {
  width: 0.5rem;
  height: 0.5rem;
  position: absolute;
}

/* Track */
::-webkit-scrollbar-track {
  /* background: rgba(255, 255, 255, 33%); */
  background: #f3f3f3;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #ccc;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
