export default (accessCode) =>
  accessCode
    .toString()
    .match(/\d{1,3}/g)
    .join("-");
