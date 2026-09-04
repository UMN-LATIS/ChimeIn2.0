# ChimeIn for PowerPoint

ChimeIn's PowerPoint add-in lets you add a live ChimeIn question directly onto a slide. When you present the slideshow, your students see the question right there on the slide, and you can open the question, close it, and reveal results without leaving PowerPoint.

::: tip Pilot feature
The PowerPoint add-in isn't published to the Microsoft store yet, so it has to be installed manually ("sideloaded"). See [Installing the add-in](#installing-the-add-in) below.
:::

## How it works

- **Sign in once per computer.** The first time you insert a ChimeIn question on a given computer, you'll be asked to sign in. After that, PowerPoint remembers you.
- **Pick a chime once per file.** The first ChimeIn question you add to a presentation asks which chime to use. Every question you add after that, in the same file, skips straight to picking a question.
- **Everything happens in the widget.** Opening and closing the question, and showing results, are all buttons right on the slide - there's no separate control panel to manage.
- **Sharing the file shares access to that chime.** The add-in only ever grants access to the one chime you picked for that presentation, not your whole ChimeIn account. See [Security notes](#security-notes).

## Adding a question to a slide

1. Open the slide where you want the question to appear.
2. On the **Insert** tab, select **My Add-ins**, then choose **ChimeIn Question**.
3. The first time you do this on a computer, you'll be asked to sign in with your normal ChimeIn account.
4. Choose the chime, then the folder, then the question you want to show. Questions you've already placed elsewhere in this presentation are marked so you don't accidentally duplicate one.
5. The question appears on the slide. Resize it like any other object.

To add more questions, repeat these steps. Because you already signed in and picked a chime, you'll go straight to the question picker.

## Presenting

Start the slideshow as usual. When you reach a slide with a ChimeIn question, use the **Open Question** button to let students respond, and **Show Results** to reveal the results in place. There are no next/previous buttons in the widget - use PowerPoint's normal slide navigation to move around; the widget just tracks the one question it was given.

## Security notes

- The chime access embedded in a presentation only allows reading and presenting questions in *that one chime* - not your other chimes, and not chime settings, editing, or grading.
- If you share a presentation file, whoever opens it can present and view results for that chime, the same as sharing a login link. Don't share ChimeIn presentation files outside your co-instructors.
- You can revoke a presentation's access at any time from **Chime settings > PowerPoint add-in** without affecting your other presentations or your ChimeIn login.

## Installing the add-in

Because this add-in isn't in the Microsoft store yet, it must be sideloaded - a supported, Microsoft-documented way of installing an add-in from outside the store. You only need to do this once per computer.

### Windows

Sideloading on Windows requires trusting a shared folder that contains the add-in's manifest file. Ask your ChimeIn administrator for the manifest file and a shared network folder path, or set one up yourself:

1. Get the `manifest.xml` file for the ChimeIn add-in (download it from `https://<your-chimein-domain>/office/manifest.xml`) and save it into a folder that's shared on your network with at least Read/Write access. See Microsoft's [Share a folder](https://learn.microsoft.com/en-us/office/dev/add-ins/testing/create-a-network-shared-folder-catalog-for-task-pane-and-content-add-ins#share-a-folder) instructions if you need to create the share.
2. In PowerPoint, open the **File** tab, choose **Options > Trust Center > Trust Center Settings > Trusted Add-in Catalogs**.
3. Enter the full network path to that shared folder (for example, `\\your-server\ChimeInAddin`) in **Catalog Url**, choose **Add catalog**, check **Show in Menu**, then **OK** out of both dialogs.
4. Restart PowerPoint.
5. Open a presentation, go to **Insert > My Add-ins > SHARED FOLDER**, select **ChimeIn Question**, and choose **Add**.

Full details, including a registry-script option for deploying this to many computers at once, are in Microsoft's [Sideload Office Add-ins on Windows from a network share](https://learn.microsoft.com/en-us/office/dev/add-ins/testing/create-a-network-shared-folder-catalog-for-task-pane-and-content-add-ins) article.

### Mac

1. Get the `manifest.xml` file for the ChimeIn add-in (download it from `https://<your-chimein-domain>/office/manifest.xml`).
2. In Finder, press <kbd>Cmd</kbd>+<kbd>Shift</kbd>+<kbd>G</kbd> and go to:
   ```
   /Users/<your-username>/Library/Containers/com.microsoft.Powerpoint/Data/Documents/wef
   ```
   Create the `wef` folder if it doesn't already exist.
3. Copy the `manifest.xml` file into that `wef` folder.
4. Open PowerPoint (or restart it if it's already running) and open a presentation.
5. Go to **Insert > My Add-ins**, and select **ChimeIn Question**.

Full details are in Microsoft's [Sideload Office Add-ins on Mac for testing](https://learn.microsoft.com/en-us/office/dev/add-ins/testing/sideload-an-office-add-in-on-mac) article.

### Removing the add-in

Removing a sideloaded add-in just clears PowerPoint's add-in cache; it doesn't affect any questions you've already placed on slides. See Microsoft's [Clear the Office cache](https://learn.microsoft.com/en-us/office/dev/add-ins/testing/clear-cache) article for your platform.

### For IT / department deployment

Once ChimeIn for PowerPoint has been through a pilot, it can be deployed to a whole department or campus without per-computer sideloading, using [Centralized Deployment](https://learn.microsoft.com/en-us/microsoft-365/admin/manage/centralized-deployment-of-add-ins) through the Microsoft 365 admin center's **Integrated apps** page, pointing at the same `manifest.xml` URL. [Contact us](mailto:latistecharch@umn.edu) if your department would like to arrange this.
