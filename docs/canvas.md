# Using ChimeIn in Canvas

ChimeIn has direct Canvas integration, which makes it easy to create and manage Chimes. In addition, ChimeIn can share completion data back to the Canvas gradebook, allowing ChimeIn responses to be used to track attendance or participation.

We have a screencast embedded in this help page, if you'd prefer to follow along that way.

::: tip Changes for Summer/Fall 2022
If you've used ChimeIn with Canvas in the past, you might remember going through a step of adding ChimeIn to your course in your Canvas settings. That's no longer necessary - you can start using ChimeIn immediately.
:::

## Adding a ChimeIn Assignment in Canvas

ChimeIn is available as an "external tool" within the Canvas assignment tool. The screencast below shows the process for creating assignments with ChimeIn, and using ChimeIn within your Canvas course.

<iframe width="100%" height="420" src="https://www.youtube.com/embed/Wf9YrZ1FAqw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

### Step by Step instructions

Below is a set of a step-by-step instructions for making a ChimeIn assignment in Canvas.

| Step                                                                                                                                   |                                         Screenshot                                          |
| -------------------------------------------------------------------------------------------------------------------------------------- | :-----------------------------------------------------------------------------------------: |
| Add a new assignment within Canvas                                                                                                     |                       ![Add an assignment](./step1-addassignment.png)                       |
| Set a point value and grading basis if you'd like                                                                                      |                            ![Grading basis](./step2-grades.png)                             |
| Set "Submission Type" to "External Tool"                                                                                               |                       ![Submission Type](./step3-submission-type.png)                       |
| Click "Find"                                                                                                                           |                               ![Click Find](./step4-find.png)                               |
| Select "ChimeIn" in the list and click **Select**                                                                                      |                           ![Select ChimeIn](./step5-chimein.png)                            |
| Check the box for "open in a new tab" (**important**)                                                                                  |                         ![Load in a new tab](./step6-checkbox.png)                          |
| Verify that Submission Attempts is set to "Unlimited"                                                                                  | ![Submission Attempts set to Unlimited](./step6.1-canvas-submission-attempts-unlimited.png) |
| Click "save" at the bottom of the page                                                                                                 |                            ![Save and Display](./step7-save.png)                            |
| Click the "load" button to open ChimeIn                                                                                                |                                  ![Load](./step8-load.png)                                  |
| Add your questions within ChimeIn. If you have co-instructors, they will automatically be granted the access to add questions as well. |      ![Click Add Question button to add your questions](./step9-add-new-question.png)       |
| **Don't forget to publish your assignment** when you're ready for your students to view it                                             |                                ![Load](./step10-publish.png)                                |

## Gradebook integration options

When you first setup ChimeIn in Canvas, you'll be asked to select how you'd like ChimeIn to appear in the gradebook. You've got two options - you can either have a single gradebook entry for all of your ChimeIn responses, or you can have multiple assignments each with its own gradebook entry. You can also choose not to have any grades.

## Approaches to using ChimeIn within Canvas

If you're using ChimeIn repeatedly throughout a course, there are a couple different approaches to setting up your Canvas course. If you'd like to keep track of student participation on a weekly or per-lecture basis, you should create separate assignments. Just repeat the steps in the previous section each time. That way, the gradebook will reflect participation in individual course sessions. Alternatively, you can create a single assignment for an entire semester, and collect an overall grade. If you'd like to discuss different approaches, just [contact us](mailto:latistecharch@umn.edu).

## Presenting to students

When you're ready to share your questions with your students, remind them to follow the assignment link within Canvas. **This is important in order for them to receive credit in the gradebook.** As the instructor, you can follow the same link to present the questions. At that point, the process is the same as presenting ChimeIn outside of Canvas. We've got a [step by step cheatsheet you can refer to](cheatsheet).

ChimeIn grades will be updated in Canvas approximately 3 hours after the last response is received. This is done to ensure that students don't get flooded with "grade update" notifications during a class. You can also use the "Force Canvas Sync" button, available under "Chime settings" or "Folder Settings" to force an instant update.

::: tip Student View  
The "Student View" option in Canvas won't work with a third party tool like ChimeIn. To see exactly what your students will see, you can use the "participant view" button in ChimeIn.
:::

## "Correct" answers and the gradebook

ChimeIn is most often used to check engagement or collect feedback on a topic, rather than as a substitute for a quiz tool. However, when using multiple choice questions, you can choose to mark some responses as "correct". In your [Chime settings](managing-a-chime), you can choose to only count correct answers towards grades in Canvas.

## Cloning a Canvas course

If you've cloned a Canvas course from a previous semester, and would like to re-use your ChimeIn questions, simply click on one of your ChimeIn assignments. ChimeIn will ask if you'd like to import the questions and folders from a previous Chime.

We recommend click through each of your ChimeIn assignments from Canvas to ensure they're linked correctly, but that should generally happen automatically.

## Grading FAQ

<details>
<summary>How long does it take for ChimeIn to sync grades with Canvas?</summary>

ChimeIn will automatically sync any assignment with new responses **3 hours after the last response**.

</details>

<details>
<summary>Can I manually resync grades now instead of waiting?</summary>

You can manually resync grades in ChimeIn.

If you setup ChimeIn for **Multiple Grade Columns (default)**:

1. Go to [ChimeIn](https://chimein.cla.umn.edu) and choose your course.
2. Choose the folder (assignment) you wish to sync.
3. Within the folder, click **Folder Settings** to open the settings menu, then choose **Force Sync with Canvas**:

  <img src="./img/faq-force-sync-folder.png" alt="Force Sync Folder" width="400" />

If you setup ChimeIn for **One Grade Column**

1. Go to [ChimeIn](https://chimein.cla.umn.edu) and choose your course.
2. Choose **Chime Settings** to open the settings menu, then click **Force Sync**:

  <img src="./img/faq-force-sync-chime.png" alt="Force Sync Chime" width="400" />

</details>

<details>
<summary id="faq-adjust-chimein-grades">How can I manually adjust ChimeIn grades?</summary>

You might have a student with an excused absence, or may want to round up some ChimeIn grades. You can tweak ChimeIn grades in the Canvas gradebook a couple of different ways:

1. Probably the easiest to **edit the grades directly** in the Canvas gradebook, with a few caveats. ChimeIn automatically re-syncs assignments a few hours after any new responses, so be sure to close questions to avoid new responses which will overwrite your edits.

   For example, I could type "ex" in the grade column to excuse a student from the ChimeIn assignment:

   <img src="./img/faq-manual-adjustment-excusing.png" width="400" alt="manual adjusting the gradebook example. Typing 'ex' will directly in the column will excuse the student from an assignment." />

   > [!IMPORTANT]
   > ChimeIn will resync an assignment if there are any new responses, so wait
   > until all your responses are in and your question is closes before making
   > adjustments.

2. Another option is to **create a separate grade column** specifically for any ChimeIn point adjustments, and then assign points as needed to that column. Since this is a separate column, you don't need to worry about ChimeIn syncing overwriting your changes.

   <img src="./img/faq-manual-adjustment-new-col.png" alt="The Canvas gradebook contains a column for ChimeIn responses, and another separate column for ChimeIn adjustments. The first user earned 7.5/10 points for ChimeIn, but the instructor rounded up their score to full points by adding another 2.5 points to the ChimeIn adjustments column." width="400" />

</details>

<details>
<summary>How can I round up <em>any</em> participation to 100%?</summary>

You may want to set up ChimeIn to award full points to a specific assignment – even if it normally only awards point for correct answers.

You can use [Curve Grades](https://community.canvaslms.com/t5/Instructor-Guide/How-do-I-curve-grades-in-the-Gradebook/ta-p/745) in Canvas to round up:

1. Go to Your Course > Grades.
2. Find the ChimeIn assignment you wish to adjust, and click the &vellip; next to the assignment name.
3. Choose "Curve Grades" from the menu:
   <img src="./img/faq-select-curve-grades.png" alt="Curve Grades shown in drop-down grade menu" width="300" />
4. Set your curve average as full points (e.g. 10/10), and check the assign 0's to non-participants box.
   <img src="./img/faq-grade-curve-settings.png" alt="Curve Grade Settings"  width="300" />
5. Apply the settings by clicking the "Curve Grades" button.

| Before                                                                                                                          | After                                                                                         |
| ------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| <img src="./img/faq-before-curve.png" alt="Before Curve: scores are 7.5/10, 6/10, and -/10 (indicating no score)" width="300"/> | <img src="./img/faq-after-curve.png" alt="After Curve: 10/10, 10/10, and 0/10" width="300" /> |

</details>

<details>
<summary>How can I drop lowest ChimeIn assignment scores?</summary>

If you set up a ChimeIn assignment for each class meeting in Canvas, you may want to permit students to miss a certain number of before it impacts their grades.

You can use [Canvas assignment groups](https://community.canvaslms.com/t5/Instructor-Guide/How-do-I-add-an-assignment-group-in-a-course/ta-p/970) to group all your chime assignments together, and then [create a rule to drop the lowest X chimes](https://community.canvaslms.com/t5/Instructor-Guide/How-do-I-create-rules-for-an-assignment-group/ta-p/848).

Here's a demo:

<iframe width="100%" height="420" src="https://www.youtube.com/embed/RXczYrezKmY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

</details>
