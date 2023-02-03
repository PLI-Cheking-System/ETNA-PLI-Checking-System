
    document.querySelector('#save').addEventListener('click', () => { // select the save button
       saveCalendar(); // call the saveCalendar function
        });

    function saveCalendar(ClassSchedule) {
        document.querySelector('#calendar').style.width = '125vh'; // resize the calendar to fit the pdf
        document.querySelector('#calendar').style.height = '250vh';
        document.querySelector('#save-button').style.display = 'none'; // hide the save button
        let calendar = document.querySelector('#all'); // select the calendar with the title
        console.log(calendar);
        html2canvas(calendar).then(canvas => { // convert the calendar to image (like a screenshot)
        console.log(canvas);
        let imgData = canvas.toDataURL('image/png'); 
        let pdf = new jsPDF('p', 'mm', 'a4'); 
        let width = pdf.internal.pageSize.getWidth();
        let height = pdf.internal.pageSize.getHeight();
        console.log(width, height);
        pdf.addImage(imgData, 'PNG', 0, 0, width, height); // add the image to the pdf
        const ClassName = document.querySelector('#className').innerHTML; // get the class name for the name of the pdf
        pdf.save(ClassName + '.pdf');
        document.querySelector('#calendar').style.width = "75%"; // resize the calendar to the original size
        document.querySelector('#calendar').style.height = "75%";
        document.querySelector('#save-button').style.display = 'block'; // show the save button
        return pdf;
        });

    }
    
