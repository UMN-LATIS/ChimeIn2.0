import socketioClient from "socket.io-client";

export default function registerSocketIOClient() {
  window.io = socketioClient;
}
