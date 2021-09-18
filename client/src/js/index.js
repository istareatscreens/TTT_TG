const init = () => {
  const canvas = document.getElementById("ttt-canvas");
  const context = canvas.getContext("2d");

  const height = window.innerHeight;
  const width = window.innerWidth;
  const lineStroke = 10;
  canvas.height = height;
  canvas.width = width;

  context.fillRect(width / 3, 0, lineStroke, height);
  context.fillRect((width * 2) / 3, 0, lineStroke, height);
  context.fillRect(0, height / 3, width, lineStroke);
  context.fillRect(0, (height * 2) / 3, width, lineStroke);
  context.fillRect(black);
};

init();
