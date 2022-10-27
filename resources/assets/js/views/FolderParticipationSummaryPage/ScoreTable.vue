<template>
  <div class="table-responsive table-container">
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
            Q{{ index + 1 }}
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
.table td,
.table th {
  border-top: 0;
  border-bottom: 1px solid #dee2e6;
}

.table-container {
  border: 1px solid #ddd;
  background: #fff;
  padding: 2rem;
  width: fit-content;
  min-width: 40rem;
}
</style>
