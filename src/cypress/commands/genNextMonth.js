const months = [
  "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
  "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
];

function getNextMonth() {
  const currentDate = new Date();
  const currentMonth = currentDate.getMonth(); 
  const nextMonth = (currentMonth + 1) % 12; 
  return months[nextMonth];
}

module.exports = getNextMonth;