jQuery(document).ready(function ($) {
  var currentPage = 1;
  var postsPerPage = 3;
  var totalPages = $(".coming-soon-events").data("pages");
  var location = $(".coming-soon-events").data("location");

  // Get next events
  $(document).on("click", ".next_coming_events", function () {
    currentPage++;
    loadEvents(currentPage, postsPerPage);
    showNavigation(currentPage);
  });

  // Get previous events
  $(document).on("click", ".previous_coming_events", function () {
    currentPage--;
    loadEvents(currentPage, postsPerPage);
    showNavigation(currentPage);
  });

  // Get all events
  $(document).on("click", ".all_events", function () {
    console.log("click all");
    loadEvents(currentPage, -1);
  });

  function loadEvents(currentPage, postsPerPage) {
    $.ajax({
      url: getEvents.ajax_url,
      type: "POST",
      data: {
        action: "ajax_handler",
        page: currentPage,
        quantity: postsPerPage,
        location: location,
      },
      success: function (response) {
        if (response.success) {
          var events = response.data;
          var container = $("#events-container");
          container.empty();
          showPosts(events);
        }
      },
      error: function () {
        console.error("Failed to load events.");
      },
    });
  }

  function showNavigation(currentPage) {
    if (currentPage > 1) {
      $(".previous_coming_events").show();
    } else {
      $(".previous_coming_events").hide();
    }

    if (currentPage >= totalPages) {
      $(".next_coming_events").hide();
    } else {
      $(".next_coming_events").show();
    }
  }

  function displayDate(start_date, end_date) {
    const startDate = new Date(start_date);
    const endDate = new Date(end_date);

    const startDay = startDate.getDate();
    const startMonth = startDate.toLocaleString("default", { month: "short" });
    const startYear = startDate.getFullYear();

    const endDay = endDate.getDate();
    const endMonth = endDate.toLocaleString("default", { month: "short" });
    const endYear = endDate.getFullYear();

    if (startYear !== endYear) {
      return `${startDay}. ${startMonth} ${startYear} - ${endDay}. ${endMonth} ${endYear}`;
    }

    if (startMonth !== endMonth) {
      return `${startDay}. ${startMonth} - ${endDay}. ${endMonth} ${endYear}`;
    }

    if (startMonth === endMonth) {
      if (startDay !== endDay) {
        return `${startDay} - ${endDay}. ${endMonth} ${endYear}`;
      }

      if (startDay === endDay) {
        return `${startDay}. ${endMonth} ${endYear}`;
      }
    }

    return "no date found";
  }

  function displayTime(start_time, end_time) {
    const startTime = new Date(`1970-01-01T${start_time}`);
    const endTime = new Date(`1970-01-01T${end_time}`);

    const formatTime = (date) => {
      let hours = date.getHours().toString().padStart(2, "0");
      let minutes = date.getMinutes().toString().padStart(2, "0");
      return `${hours}:${minutes}`;
    };

    return `${formatTime(startTime)} - ${formatTime(endTime)}`;
  }

  function showPosts(events) {
    $(".events-list").empty();
    events.forEach(function (event) {
      var eventHtml =
        displayStart(event) +
        displayImage(event) +
        displayContentWrapperStart() +
        displayTitle(event) +
        displayDateDetails(event) +
        displayTimeDetails(event) +
        displayLocationDetails(event) +
        displayContentWrapperEnd()+
        displayEnd();
      $(".events-list").append(eventHtml);
    });
  }

  function displayStart(event) {
    let id = event.id;
    let link = event.permalink;
    let html = `<a href="${link}" class="event" data-event="${id}">`;
    return html;
  }

  function displayImage(event) {
    let image = event.featured_image;
    let html = `<div class="image-wrapper">
                    <img src="${image}" alt="">
                </div>`;
    return html;
  }

  function displayContentWrapperStart() {
    let html = `<div class="content-wrapper">`;
    return html;
  }

  function displayContentWrapperEnd() {
    let html = `</div>`;
    return html;
  }

  function displayTitle(event) {
    let title = event.title;
    let html = `<h3>${title}</h3>`;
    return html;
  }

  function displayDateDetails(event) {
    let date = displayDate(event.meta.date_start, event.meta.date_end);
    let html = ` <div class="date details">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                    </svg>
                    <span>${date}</span>
                </div>`;
    return html;
  }

  function displayTimeDetails(event) {
    let time = displayTime(event.meta.time_start, event.meta.time_end);
    let html = `
        <div class="time details">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
            </svg>
            <span>${time}</span>
        </div>
    `;
    return html;
  }

  function displayLocationDetails(event) {
    let location = event.location;
    if (!location) {
      return "";
    }
    html = `
        <div class="location details">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
            </svg>
            <span>${location}</span>
        </div>
    `;
    return html;
  }

  function displayEnd() {
    return '</a>';
  }
});
