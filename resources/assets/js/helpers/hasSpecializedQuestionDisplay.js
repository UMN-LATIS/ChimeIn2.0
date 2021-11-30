/**
 * if question requires additional component for rendering prompt
 */
export default (questionType) => {
  return ["multiple_choice", "heatmap_response"].includes(questionType);
};
