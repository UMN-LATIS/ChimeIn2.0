describe("question", () => {
  it("creates a question");
  it("opens a question");
  it("edits a question");
  it("deletes a question");
  it("changes the folder");
  it("allows multiple reponses");
  it("supports rich text formatting in the question text");
  it("allows LaTeX in the question text");
  it("creates an anonymous question");

  describe("multiple choice", () => {
    it("adds a multiple choice question with responses");
    it("marks correct responses");
    it("edits responses");
    it("removes respones");
  });

  describe("free response question", () => {
    it("creates a free response question");
    it("hides wordcloud");
  });

  describe("text heatmap", () => {
    it("creates a text heatmap question");
    it("lets participants select parts of heatmap text");
  });

  describe("image response", () => {
    it("creates an image response question");
  });

  describe("slider", () => {
    it("creates a slider question");
    it("sets the left label");
    it("sets the right label");
    it("lets range be quantitative");
  });

  describe("image heatmap", () => {
    it("creates an image heatmap question");
    it("lets user change image");
  });

  describe("no response", () => {
    it("creates a no response / placeholder");
  });
});
