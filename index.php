<?php
function build_calendar($month, $year) {
    
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    
    // Create array containing names of all days in a week
    // start on sunday(*)
    //$daysOfWeek = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    // start on monday(*)
    $daysOfWeek = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    
    // Get first day of the month passed as argument
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    
    // Number of days the month contains
    $numberDays = date('t', $firstDayOfMonth);
    
    // Getting some info about the first day on the month
    $dateComponents = getdate($firstDayOfMonth);
    
    // Get the name of the month
    $monthName = $dateComponents['month'];
    
    // Getting the index value 0-6 of the first day of the month
    $dayOfWeek = $dateComponents['wday'];
    // Start on monday visual calendar(*)
    if($dayOfWeek == 0) {
        $dayOfWeek = 6;
    }
    else {
        $dayOfWeek = $dayOfWeek - 1;
    }
    
    // Getting the current date
    $dateToday = date('Y-m-d');
    
    // Now creating the HTML table
    $calendar = "<table class='table table-bordered'>";
    $calendar .= "<center><h2>$monthName $year</h2>";
    
    $calendar .= "<a class='btn btn-xs btn-primary' href='?month=".date('m', mktime(0, 0, 0, $month-1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month-1, 1, $year))."'>Previous Month</a> ";
    
    $calendar .= " <a class='btn btn-xs btn-primary' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a> ";
    
    $calendar .= "<a class='btn btn-xs btn-primary' href='?month=".date('m', mktime(0, 0, 0, $month+1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month+1, 1, $year))."'>Next Month</a></center><br>";
    
    $calendar.="<tr>";
    
    // Creating the calendar headers
    foreach($daysOfWeek as $day) {
        $calendar .= "<th class='header'>$day</th>";
    }
    
    $calendar .= "</tr><tr>";
    
    // The variable $dayOfWeek will make sure that there must be only 7 columns on our table
    if($dayOfWeek > 0) {
        for($k=0; $k < $dayOfWeek; $k++) {
            $calendar .= "<td></td>";
        }
    }
    
    // Initiating the day counter
    $currentDay = 1;
    
    // Getting the month number
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while($currentDay <= $numberDays) {
        
        // if seventh column (saturday) reached, start a new row
        if($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
        
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";
        
        $dayname = strtolower(date('l', strtotime($date)));
        $eventNum = 0;
        $today = $date == date('Y-m-d') ? "today" : "";
        if($dayname == 'saturday' || $dayname == 'sunday') {
            $calendar .= "<td><h4>$currentDay</h4><button class='btn btn-danger btn-xs'>Holiday</button>";
        }
        else if($date < date('Y-m-d')) {
            $calendar .= "<td><h4>$currentDay</h4><button class='btn btn-danger btn-xs'>N/A</button>";
        }
        else {
            $totalBookings = checkSlots($mysqli, $date);
            if($totalBookings == 2) {
                $calendar .= "<td class='$today'><h4>$currentDay</h4><a href='#' class='btn btn-danger btn-xs'>All Booked</a>";
            }
            else {
                $calendar .= "<td class='$today'><h4>$currentDay</h4><a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Book</a>";
            }
        }
        
        $calendar .= "</td>";
            
        // Incrementing the counters
        $currentDay++;
        $dayOfWeek++;
    }
    
    if($dayOfWeek != 7) {
        $remainingDays = 7-$dayOfWeek;
        for($i = 0; $i < $remainingDays; $i++) {
            $calendar .= "<td></td>";
        }
    }
    
    $calendar .= "</tr>";
    $calendar .= "</table>";
    
    echo $calendar;
}

function checkSlots($mysqli, $date) {
    
    $stmt = $mysqli->prepare("SELECT * FROM bookings WHERE date = ?");
    $stmt->bind_param('s', $date);
    $totalBookings = 0;
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $totalBookings++;
            }
            
            $stmt->close();
        }
    } 
    
    return $totalBookings;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    
    <style>
        table {
            table-layout: fixed;
        }
        
        td {
            width: 33%;
        }
        
        .today {
            background: yellow;
        }
    </style>
    
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                    $dateComponents = getdate();
                     if(isset($_GET['month']) && isset($_GET['year'])){
                         $month = $_GET['month']; 			     
                         $year = $_GET['year'];
                     }else{
                         $month = $dateComponents['mon']; 			     
                         $year = $dateComponents['year'];
                     }
                    echo build_calendar($month,$year);
                
                ?>
            </div>
        </div>
    </div>
</body>
</html>