<!DOCTYPE html>
<html lang="{{{ Session::get('langID') }}}">
	<head>
		<meta charset="utf-8">
	</head>
	<body style="font-family: Tahoma">
		<div>
			<p>Klaim pemenang Turnamen Tangkas</p>
			<table>
				<tbody>
					<tr>
						<td>Username</td>
						<td>: {{{ $username }}}</td>
					</tr>
					<tr>
						<td>Peringkat</td>
						<td>: {{{ $rank }}}</td>
					</tr>
					<tr>
						<td>Hadiah</td>
						<td>: {{{ $prize }}}</td>
					</tr>
					<tr>
						<td>Bank</td>
						<td>: {{{ $bank }}}</td>
					</tr>
					<tr>
						<td>Nama Rekening</td>
						<td>: {{{ $bankName }}}</td>
					</tr>
					<tr>
						<td>Number Rekening</td>
						<td>: {{{ $bankNo }}}</td>
					</tr>
					<tr>
						<td>No. Hp</td>
						<td>: {{{ $phoneNo }}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>
