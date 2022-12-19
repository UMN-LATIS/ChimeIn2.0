export default (word: string, amount: number) =>
  amount === 1 ? word : `${word}s`;
