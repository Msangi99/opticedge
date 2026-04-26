// Optic Edge — optional "assign product to agent" flow with camera capture + IMEI scan + API validation.
//
// Add to pubspec.yaml:
//   dependencies:
//     http: ^1.2.0
//     image_picker: ^1.0.7
//     google_mlkit_barcode_scanning: ^0.12.0
//
// Android: <uses-permission android:name="android.permission.CAMERA" />
// iOS: NSCameraUsageDescription, NSPhotoLibraryUsageDescription in Info.plist
//
// API base should include /api (e.g. https://your-host.com/api). Use Sanctum Bearer token from admin login.

import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:google_mlkit_barcode_scanning/google_mlkit_barcode_scanning.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

/// Decodes 1D/2D barcodes from a still image on-device (Code128, QR, etc.).
Future<List<String>> scanBarcodesFromImageFile(String imagePath) async {
  final input = InputImage.fromFilePath(imagePath);
  final scanner = BarcodeScanner(formats: [BarcodeFormat.all]);
  try {
    final codes = await scanner.processImage(input);
    final out = <String>[];
    final seen = <String>{};
    for (final b in codes) {
      final v = (b.rawValue ?? '').trim();
      if (v.isNotEmpty && seen.add(v)) {
        out.add(v);
      }
    }
    return out;
  } finally {
    await scanner.close();
  }
}

String _joinApi(String baseUrlApi, String relativePath) {
  final b = baseUrlApi.replaceAll(RegExp(r'/+$'), '');
  final p = relativePath.replaceAll(RegExp(r'^/+'), '');
  return '$b/$p';
}

/// Server-side QR decode (PHP GD + ZXing). Use when on-device scan returns nothing.
Future<List<String>> decodeQrOnServer({
  required String baseUrlApi,
  required String bearerToken,
  required File imageFile,
}) async {
  final uri = Uri.parse(_joinApi(baseUrlApi, 'admin/barcodes/decode-image'));
  final req = http.MultipartRequest('POST', uri)
    ..headers['Authorization'] = 'Bearer $bearerToken'
    ..headers['Accept'] = 'application/json'
    ..files.add(await http.MultipartFile.fromPath('image', imageFile.path));
  final streamed = await req.send();
  final body = await streamed.stream.bytesToString();
  if (streamed.statusCode >= 400) {
    throw Exception('decode-image failed (${streamed.statusCode}): $body');
  }
  final map = jsonDecode(body) as Map<String, dynamic>;
  final data = map['data'];
  if (data is! List) {
    return [];
  }
  return data
      .map((e) => (e is Map && e['code'] != null) ? e['code'].toString().trim() : '')
      .where((s) => s.isNotEmpty)
      .toSet()
      .toList();
}

Future<ValidateImeiResponse> validateAssignableImei({
  required String baseUrlApi,
  required String bearerToken,
  required int productId,
  required String scannedText,
}) async {
  final uri = Uri.parse(_joinApi(baseUrlApi, 'admin/agents/assignments/validate-imei'));
  final res = await http.post(
    uri,
    headers: {
      'Authorization': 'Bearer $bearerToken',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({'product_id': productId, 'imei': scannedText}),
  );
  final map = jsonDecode(res.body) as Map<String, dynamic>;
  if (res.statusCode >= 400) {
    return ValidateImeiResponse(
      ok: false,
      message: map['message']?.toString() ?? 'Invalid IMEI for this product.',
    );
  }
  final data = map['data'];
  if (data is Map && data['product_list_id'] != null) {
    return ValidateImeiResponse(
      ok: true,
      message: null,
      productListId: int.parse(data['product_list_id'].toString()),
      imeiNumber: data['imei_number']?.toString(),
    );
  }
  return ValidateImeiResponse(ok: false, message: 'Unexpected response');
}

class ValidateImeiResponse {
  ValidateImeiResponse({
    required this.ok,
    this.message,
    this.productListId,
    this.imeiNumber,
  });

  final bool ok;
  final String? message;
  final int? productListId;
  final String? imeiNumber;
}

/// Example panel: pick agent + product, optionally capture photo, scan, validate, add to selection, submit.
class AgentAssignProductScanPanel extends StatefulWidget {
  const AgentAssignProductScanPanel({
    super.key,
    required this.baseUrlApi,
    required this.bearerToken,
    required this.agents,
    required this.onAssigned,
  });

  /// e.g. https://example.com/api
  final String baseUrlApi;
  final String bearerToken;
  final List<({int id, String name})> agents;
  final VoidCallback onAssigned;

  @override
  State<AgentAssignProductScanPanel> createState() => _AgentAssignProductScanPanelState();
}

class _AgentAssignProductScanPanelState extends State<AgentAssignProductScanPanel> {
  final _picker = ImagePicker();
  List<({int id, String name})> _products = [];
  int? _agentId;
  int? _productId;
  final Set<int> _selectedListIds = {};
  String? _status;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    final uri = Uri.parse(_joinApi(widget.baseUrlApi, 'admin/agents/products-for-assign'));
    final res = await http.get(uri, headers: {'Authorization': 'Bearer ${widget.bearerToken}', 'Accept': 'application/json'});
    if (!mounted || res.statusCode >= 400) {
      return;
    }
    final list = (jsonDecode(res.body) as Map)['data'] as List? ?? [];
    setState(() {
      _products = list
          .map((e) => (id: int.parse(e['id'].toString()), name: e['name'].toString()))
          .toList();
    });
  }

  Future<void> _captureAndScan() async {
    if (_productId == null) {
      setState(() => _status = 'Select a product first.');
      return;
    }
    setState(() {
      _loading = true;
      _status = null;
    });
    try {
      final x = await _picker.pickImage(source: ImageSource.camera, imageQuality: 85);
      if (x == null) {
        setState(() => _loading = false);
        return;
      }
      var texts = await scanBarcodesFromImageFile(x.path);
      if (texts.isEmpty) {
        try {
          texts = await decodeQrOnServer(
            baseUrlApi: widget.baseUrlApi,
            bearerToken: widget.bearerToken,
            imageFile: File(x.path),
          );
        } catch (_) {
          /* server QR also failed */
        }
      }
      if (texts.isEmpty) {
        setState(() {
          _loading = false;
          _status = 'No barcode found. Try a clearer photo or type the IMEI.';
        });
        return;
      }
      for (final raw in texts) {
        final vr = await validateAssignableImei(
          baseUrlApi: widget.baseUrlApi,
          bearerToken: widget.bearerToken,
          productId: _productId!,
          scannedText: raw,
        );
        if (vr.ok && vr.productListId != null) {
          setState(() {
            _selectedListIds.add(vr.productListId!);
            _status = 'Added ${vr.imeiNumber ?? "IMEI"}';
          });
          break;
        }
      }
      if (_status == null) {
        setState(() => _status = 'Scanned codes did not match an assignable IMEI for this product.');
      }
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _submit() async {
    if (_agentId == null || _productId == null || _selectedListIds.isEmpty) {
      setState(() => _status = 'Choose agent, product, and at least one IMEI.');
      return;
    }
    setState(() {
      _loading = true;
      _status = null;
    });
    final uri = Uri.parse(_joinApi(widget.baseUrlApi, 'admin/agents/assignments'));
    final res = await http.post(
      uri,
      headers: {
        'Authorization': 'Bearer ${widget.bearerToken}',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'agent_id': _agentId,
        'product_id': _productId,
        'product_list_ids': _selectedListIds.toList(),
      }),
    );
    if (!mounted) {
      return;
    }
    setState(() => _loading = false);
    if (res.statusCode == 201) {
      setState(() {
        _selectedListIds.clear();
        _status = 'Assigned.';
      });
      widget.onAssigned();
    } else {
      final map = jsonDecode(res.body);
      setState(() => _status = map is Map ? map['message']?.toString() ?? res.body : res.body);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        DropdownButtonFormField<int>(
          value: _agentId,
          decoration: const InputDecoration(labelText: 'Agent'),
          items: widget.agents.map((a) => DropdownMenuItem(value: a.id, child: Text(a.name))).toList(),
          onChanged: (v) => setState(() => _agentId = v),
        ),
        DropdownButtonFormField<int>(
          value: _productId,
          decoration: const InputDecoration(labelText: 'Product'),
          items: _products.map((p) => DropdownMenuItem(value: p.id, child: Text(p.name))).toList(),
          onChanged: (v) => setState(() => _productId = v),
        ),
        const SizedBox(height: 8),
        OutlinedButton.icon(
          onPressed: _loading ? null : _captureAndScan,
          icon: const Icon(Icons.camera_alt_outlined),
          label: const Text('Capture photo & scan IMEI (optional)'),
        ),
        const SizedBox(height: 8),
        Text('Selected devices: ${_selectedListIds.length}'),
        if (_status != null) Text(_status!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
        const SizedBox(height: 8),
        FilledButton(
          onPressed: _loading ? null : _submit,
          child: _loading ? const SizedBox(height: 22, width: 22, child: CircularProgressIndicator(strokeWidth: 2)) : const Text('Assign to agent'),
        ),
      ],
    );
  }
}
