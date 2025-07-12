      <table border="0" cellspacing="0" cellpadding="0" style="min-width:1000px; max-width:1200px;">
        <tr>
          <td colspan="2" class="text">Date</td>
          <td class="text" title="Actual time spent">Act.Hrs.</td>
          <td class="text" title="Adjusted time spent">Adj.Hrs.</td>
          <td class="text" title="Chargeable time spent">Bill.Hrs.</td>
          <td><br></td>
          <td colspan="2" class="text">Employee&nbsp;</td>
          <?php if (!isset($hide_task)) { ?><td colspan="2" class="text">Task</td><?php } ?>
          <?php if (isset($show_job)) { ?><td colspan="2" class="text">Job</td><?php } ?>
          <td class="text">Employee Comment</td>
          <td class="text">Adjusted Comment</td>
        </tr>
        <?php
		//display job history
		$total_actual = 0;
		$total_external = 0;
		$total_billable = 0;
		$time_log_staff = array();
		foreach ($all_staff as $k => $v)
			$time_log_staff[$k] = array_safe(explode(' ', $v), 0, $v);
		$odd = true;
		while ($row=mysql_fetch_object($result)) {
			$next_contact = $row->job_date;
			//format job_date
			$job_date = ToNextContact($next_contact);
			$start_time = $row->start_time;
			$end_time = $row->end_time;

			$in_progress = $end_time == null;
			$actual = $in_progress ? 0 : ((strtotime($row->end_time) - strtotime($row->start_time)) / 60);
			$external = $row->override ? $row->override : $actual;
			$billable = ($row->no_charge == 1 || $row->chargeable === '0') ? 0 : $external;
			$total_actual += $actual;
			$total_external += $external;
			$total_billable += $billable;
			
			echo "<tr class=\"text " . ( $odd ? "odd" : "even" ) . "\" valign=\"top\">";
			echo "<td width=\"70\" class=\"copy\">$job_date</td>";
			echo "<td width=\"20\">&nbsp;</td>";
			echo "<td width=\"45\">" . ($in_progress ? '-' : formatMinutes($actual)) . "</td>";
			echo "<td width=\"45\" class=\"copy\">" . ($in_progress ? '-' : formatMinutes($external)) . "</td>";
			echo "<td width=\"45\">" . ($in_progress ? '' : formatMinutes($billable)) . "</td>";
			echo "<td width=\"10\">&nbsp;</td>";
			echo "<td width=\"50\">" . array_safe($time_log_staff, $row->employee, $row->employee) . "</td>";
			echo "<td width=\"20\">&nbsp;</td>";
			if (!isset($hide_task)) {
				echo '<td class="copy"><a href="job_task_detail.php?job_task_id=' . $row->job_task_id . '" style="color:#fff;">';
				echo htmlspecialchars($row->job_task_description);
				if ($row->chargeable === '0' || $row->no_charge)
					echo ' (no charge)';
				if ($row->job_task_number)
					echo ' [Ref# ' . $row->job_task_number . ']';
				if ($row->ext_description)
					echo '<span style="display:none;">' . ($row->job_task_description ? ' - ' : '') . htmlspecialchars($row->ext_description) . '</span>';
				elseif ($row->description)
					echo '<span style="display:none;">' . ($row->job_task_description ? ' - ' : '') . htmlspecialchars($row->description) . '</span>';
				echo "</a></td>";
				echo "<td width=\"20\">&nbsp;</td>";
			}
			if (isset($show_job)) {
				echo '<td><a href="job_control_detail.php?job_id=' . $row->job_id . '" style="color:#fff;">' . htmlspecialchars($row->job_title) . '</a></td>';
				echo "<td width=\"20\">&nbsp;</td>";
			}
			echo "<td width=\"20%\">" . htmlspecialchars($row->description) . "</td>";
			echo "<td width=\"20%\">" . htmlspecialchars($row->ext_description) . "</td>";
			echo "<td width=\"20\">&nbsp;</td>";
			echo '<td align="right" class="copyLink" style="display:none;"><a href="#" onclick="return copyTime(this)" style="color:#ffffff">copy</a></td>';
			echo "</tr>";
			$odd = !$odd;
		}
		?>
        <tr>
          <td class="text">Total:</td>
          <td>&nbsp;</td>
          <td class="text"><?= formatMinutes($total_actual) ?></td>
          <td class="text"><?= formatMinutes($total_external) ?></td>
          <td class="text"><?= formatMinutes($total_billable) ?></td>
        </tr>
      </table>