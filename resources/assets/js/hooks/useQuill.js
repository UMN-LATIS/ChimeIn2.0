import { onMounted, ref } from "vue";
import Quill from "quill";
// import BlotFormatter from "quill-blot-formatter";
import QuillImageUploader from "quill-image-uploader";
import mergeDeepRight from "ramda/es/mergeDeepRight.js";
import "quill/dist/quill.snow.css";

const defaultModules = [
  // {
  //   name: "blotFormatter",
  //   module: BlotFormatter,
  // },
  {
    name: "imageUploader",
    module: QuillImageUploader,
  },
];

function registerQuillModules(modules, suppressWarning = false) {
  modules.forEach((module) => {
    Quill.register(`modules/${module.name}`, module.module, suppressWarning);
  });
}

// register all default quill modules on first invocation
registerQuillModules(defaultModules, true);

export default function useQuill({
  editorContainerRef,
  options,
  modules,
} = {}) {
  const imageHandlers = ref([]);
  const textChangeHandlers = ref([]);

  const defaultOptions = {
    modules: {
      toolbar: [
        ["bold", "italic", "underline", "align"],
        [{ list: "ordered" }, { list: "bullet" }],
        [{ script: "sub" }, { script: "super" }, "formula"],
        ["link", "image"],
      ],
      // blotFormatter: {},
      imageUploader: {
        upload: (file) => {
          return Promise.all(
            imageHandlers.value.map((handler) => handler(file))
          );
        },
      },
    },
    theme: "snow",
  };

  const quill = ref(null);
  registerQuillModules(modules);

  const onTextChange = (fn) => {
    textChangeHandlers.value.push(fn);

    // return a function to remove handler
    return () => {
      textChangeHandlers.value = textChangeHandlers.value?.filter(
        (handler) => handler !== fn
      );
    };
  };

  const onAttachImage = (fn) => {
    imageHandlers.value.push(fn);

    // return a function to remove image handler if needed
    return () => {
      imageHandlers.value = imageHandlers.value?.filter(
        (handler) => handler !== fn
      );
    };
  };

  const setText = (text) => {
    quill.value.setText(text);
  };

  const setHTML = (html) => {
    if (!quill.value) {
      console.error("Canot set HTML before quill is initialized");
      return;
    }
    quill.value.root.innerHTML = html;
  };

  const getHTML = () => quill.value?.root.innerHTML || "";
  const getText = () => quill.value?.getText() || "";

  onMounted(() => {
    const mergedOptions = mergeDeepRight(defaultOptions, options);
    quill.value = new Quill(editorContainerRef.value, mergedOptions);
    quill.value.on("text-change", () =>
      textChangeHandlers.value.forEach((handler) =>
        handler(quill.value?.root.innerHTML)
      )
    );
  });

  return {
    onTextChange,
    onAttachImage,
    getText,
    setText,
    getHTML,
    setHTML,
    getQuill: () => quill.value,
  };
}
